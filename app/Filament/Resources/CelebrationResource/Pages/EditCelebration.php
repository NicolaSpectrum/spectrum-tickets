<?php

namespace App\Filament\Resources\CelebrationResource\Pages;

use App\Filament\Resources\CelebrationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditCelebration extends EditRecord
{
    protected static string $resource = CelebrationResource::class;

    protected function getHeaderActions(): array
    {
        return [

            // -------- Organizer: Enviar a aprobación ----------
            Actions\Action::make('submitForApproval')
                ->label('Enviar a aprobación')
                ->visible(fn () =>
                    auth()->user()->hasRole('organizer') &&
                    $this->record->status === 'draft'
                )
                ->action(function () {
                    $this->record->update([
                        'status' => 'pending_approval',
                    ]);

                    Notification::make()
                        ->title('Celebración enviada para aprobación')
                        ->success()
                        ->send();
                }),

            // -------- Admin: Aprobar ----------
            Actions\Action::make('approve')
                ->label('Aprobar')
                ->color('success')
                ->visible(fn () => auth()->user()->hasRole('admin'))
                ->action(function () {
                    $this->record->update(['status' => 'approved']);
                    
                    Notification::make()
                        ->title('Celebración Aprobada')
                        ->success()
                        ->send();
                }),

            // -------- Admin: Rechazar ----------
            Actions\Action::make('reject')
                ->label('Rechazar')
                ->color('danger')
                ->visible(fn () => auth()->user()->hasRole('admin'))
                ->action(function () {
                    $this->record->update(['status' => 'rejected']);
                     Notification::make()
                        ->title('Celebración Rechazada')
                        ->success()
                        ->send();
                }),

            Actions\DeleteAction::make(),
        ];
    }
    protected function getFormActions(): array

    {

        return [

            $this->getSaveFormAction()->label('Guardar cambios'),
            $this->getCancelFormAction()->label('Cancelar'),
        ];

    }
}
