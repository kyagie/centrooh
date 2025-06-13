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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
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
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                // Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
