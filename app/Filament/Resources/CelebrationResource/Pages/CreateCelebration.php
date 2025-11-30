<?php

namespace App\Filament\Resources\CelebrationResource\Pages;

use App\Filament\Resources\CelebrationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCelebration extends CreateRecord
{
    protected static string $resource = CelebrationResource::class;

    public function getTitle(): string
    {
        return 'Crear Evento';
    }

    protected function getFormActions(): array
    {
        // Obtiene las acciones por defecto y modifica las etiquetas
        return [
            $this->getCreateFormAction()->label('Guardar'),
            $this->getCreateAnotherFormAction()->label('Guardar y crear otro evento'),
            $this->getCancelFormAction()->label('Cancelar'),
        ];
    }
}
