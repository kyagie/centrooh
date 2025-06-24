<?php

namespace App\Filament\Resources\AgentResource\RelationManagers;

use App\Services\Billboards\BillboardAssignmentService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;

use Illuminate\Support\Facades\DB;

class AgentDistrictsRelationManager extends RelationManager
{
    protected static string $relationship = 'agentDistricts';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('district_id')
                    ->relationship('district', 'name')
                    ->searchable()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('district.name')
            ->columns([
                Tables\Columns\TextColumn::make('district.name')
                    ->label('District Name')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
                Action::make('assignDistrict')
                    ->form([
                        Forms\Components\Select::make('district_id')
                            ->relationship('district', 'name')
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $agentId = $this->getOwnerRecord()->id;
                        $districtId = $data['district_id'];

                        // Check if this agent is already assigned to this district
                        if ($this->getOwnerRecord()->agentDistricts()->where('district_id', $districtId)->exists()) {
                            Notification::make()
                                ->title('Agent already assigned to this district')
                                ->danger()
                                ->send();

                            return;
                        }

                        DB::beginTransaction();

                        try {
                            // Create the agent district relationship
                            $agentDistrict = $this->getOwnerRecord()->agentDistricts()->create([
                                'district_id' => $districtId,
                            ]);

                            // Assign billboards using the service
                            $billboardService = app(BillboardAssignmentService::class);
                            $result = $billboardService->assignBillboardsInDistrict($agentDistrict);

                            DB::commit();

                            // Show notification based on result
                            if ($result['success']) {
                                $message = "District assigned successfully. ";
                                if ($result['assigned_count'] > 0) {
                                    $message .= "{$result['assigned_count']} billboards were assigned to the agent.";
                                } else {
                                    $message .= "No new billboards were assigned.";
                                }

                                Notification::make()
                                    ->title('Success')
                                    ->body($message)
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Warning')
                                    ->body("District assigned, but {$result['message']}")
                                    ->warning()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            DB::rollBack();

                            Notification::make()
                                ->title('Error')
                                ->body("Failed to assign district: {$e->getMessage()}")
                                ->danger()
                                ->send();
                        }
                    })
                    ->closeModalByClickingAway(false)


            ])
            ->actions([
                ActionGroup::make([
                    Action::make('detachDistrict')
                        ->label('Detach District')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Detach District')
                        ->modalDescription('Are you sure you want to detach this district from this agent? This action cannot be undone.')
                        ->modalSubmitActionLabel('Yes, detach district')
                        ->action(function ($record): void {
                            DB::beginTransaction();

                            try {
                                // Get the agent district relationship
                                $agentDistrict = $record;

                                // Use the service to detach billboards
                                $billboardService = app(BillboardAssignmentService::class);
                                $result = $billboardService->detachBillboardsInDistrict($agentDistrict);

                                // Delete the agent-district relationship
                                $agentDistrict->delete();

                                DB::commit();

                                // Show notification based on result
                                if ($result['success']) {
                                    Notification::make()
                                        ->title('Success')
                                        ->body("District detached successfully. {$result['detached_count']} billboards were detached from the agent.")
                                        ->success()
                                        ->send();
                                } else {
                                    Notification::make()
                                        ->title('Warning')
                                        ->body("Could not detach billboards: {$result['message']}")
                                        ->warning()
                                        ->send();
                                }
                            } catch (\Exception $e) {
                                DB::rollBack();

                                Notification::make()
                                    ->title('Error')
                                    ->body("Failed to detach district: {$e->getMessage()}")
                                    ->danger()
                                    ->send();
                            }
                        }),
                    Action::make('detachBillboards')
                        ->label('Detach Billboards')
                        ->icon('heroicon-o-user-minus')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Detach Billboards')
                        ->modalDescription('Are you sure you want to detach all billboards in this district from this agent? This action cannot be undone.')
                        ->modalSubmitActionLabel('Yes, detach billboards')
                        ->action(function ($record): void {
                            DB::beginTransaction();

                            try {
                                // Get the agent district relationship
                                $agentDistrict = $record;

                                // Use the service to detach billboards
                                $billboardService = app(BillboardAssignmentService::class);
                                $result = $billboardService->detachBillboardsInDistrict($agentDistrict);

                                DB::commit();

                                // Show notification based on result
                                if ($result['success']) {
                                    if ($result['detached_count'] > 0) {
                                        Notification::make()
                                            ->title('Success')
                                            ->body("{$result['detached_count']} billboards were detached from the agent.")
                                            ->success()
                                            ->send();
                                    } else {
                                        Notification::make()
                                            ->title('Information')
                                            ->body("No billboards were detached. {$result['message']}")
                                            ->info()
                                            ->send();
                                    }
                                } else {
                                    Notification::make()
                                        ->title('Warning')
                                        ->body("Could not detach billboards: {$result['message']}")
                                        ->warning()
                                        ->send();
                                }
                            } catch (\Exception $e) {
                                DB::rollBack();

                                Notification::make()
                                    ->title('Error')
                                    ->body("Failed to detach billboards: {$e->getMessage()}")
                                    ->danger()
                                    ->send();
                            }
                        }),
                    Action::make('reassignBillboards')
                    ->label('Reassign Billboards')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Reassign Billboards')
                    ->modalDescription('This will detach all current billboards and reassign only active billboards in this district to this agent. Continue?')
                    ->modalSubmitActionLabel('Yes, reassign billboards')
                    ->action(function ($record): void {
                        DB::beginTransaction();

                        try {
                            // Get the agent district relationship
                            $agentDistrict = $record;

                            // Use the service to first detach billboards
                            $billboardService = app(BillboardAssignmentService::class);
                            $detachResult = $billboardService->detachBillboardsInDistrict($agentDistrict);
                            
                            // Then reassign billboards
                            $assignResult = $billboardService->assignBillboardsInDistrict($agentDistrict);

                            DB::commit();

                            // Show notification based on results
                            if ($assignResult['success']) {
                                Notification::make()
                                    ->title('Success')
                                    ->body("Billboards reassigned successfully. {$detachResult['detached_count']} billboards were detached and {$assignResult['assigned_count']} active billboards were assigned.")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Warning')
                                    ->body("Billboards were detached, but there was an issue with reassignment: {$assignResult['message']}")
                                    ->warning()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            DB::rollBack();

                            Notification::make()
                                ->title('Error')
                                ->body("Failed to reassign billboards: {$e->getMessage()}")
                                ->danger()
                                ->send();
                        }
                    }),
                ]),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                // Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
