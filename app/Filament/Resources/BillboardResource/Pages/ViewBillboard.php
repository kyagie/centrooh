<?php

namespace App\Filament\Resources\BillboardResource\Pages;

use App\Filament\Resources\BillboardResource;
use App\Models\Billboard;
use App\Services\AgentNotificationService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewBillboard extends ViewRecord
{
    protected static string $resource = BillboardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('requestPhoto')
                ->label('Request Photo')
                ->icon('heroicon-o-camera')
                ->color('primary')
                ->form([
                    Forms\Components\Textarea::make('note')
                        ->label('Request Note')
                        ->placeholder('Add any specific instructions for the agent...')
                        ->maxLength(500),
                    Forms\Components\Placeholder::make('status_info')
                        ->label('Status Change')
                        ->content('This will change the billboard status to "pending"')
                        ->helperText('The billboard will remain in pending status until an agent uploads a new photo'),
                ])
                ->action(function () {
                    $notificationService = app(AgentNotificationService::class);
                    $result = $notificationService->requestBillboardPhotoUpdate(
                        $this->record, 
                        $this->data['note'] ?? null,
                        Auth::id()
                    );
                    
                    if ($result['success']) {
                        Notification::make()
                            ->title('Success')
                            ->body($result['message'])
                            ->success()
                            ->send();
                        $this->redirect(BillboardResource::getUrl('index'));
                    } else {
                        Notification::make()
                            ->title('Error')
                            ->body($result['message'])
                            ->danger()
                            ->send();
                    }
                })
                ->requiresConfirmation()
                ->modalHeading('Request Billboard Photo Update')
                ->modalDescription('This will send a notification to all agents assigned to this billboard requesting an updated photo and change the billboard status to "pending".'),
        ];
    }
}
