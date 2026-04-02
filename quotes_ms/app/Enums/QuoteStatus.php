<?php

namespace App\Enums;

enum QuoteStatus: int
{
    case DELETED = 0;           // Cotización eliminada
    case CLOSE = 1;             // Cotización cerrada
    case EDITING_DRAFT = 2;     // Cotización en modo edición (Borrador)
    case MERGED = 3;            // Cotización fusionada

    /**
     * Obtiene el nombre descriptivo del estado
     */
    public function label(): string
    {
        return match($this) {
            self::DELETED => 'Eliminada',
            self::CLOSE => 'Cerrada',
            self::EDITING_DRAFT => 'En Edición',
            self::MERGED => 'Fusionada',
        };
    }

    /**
     * Obtiene el nombre del color para la UI
     */
    public function color(): string
    {
        return match($this) {
            self::DELETED => 'danger',
            self::CLOSE => 'success',
            self::EDITING_DRAFT => 'warning',
            self::MERGED => 'info',
        };
    }

    /**
     * Obtiene el badge/icono para la UI
     */
    public function badge(): string
    {
        return match($this) {
            self::DELETED => '❌',
            self::CLOSE => '✅',
            self::EDITING_DRAFT => '✏️',
            self::MERGED => '🔄',
        };
    }

    /**
     * Obtiene la descripción del estado
     */
    public function description(): string
    {
        return match($this) {
            self::DELETED => 'La cotización ha sido eliminada',
            self::CLOSE => 'La cotización está cerrada y guardada',
            self::EDITING_DRAFT => 'La cotización está en modo borrador y puede ser editada',
            self::MERGED => 'La cotización ha sido fusionada',
        };
    }

    /**
     * Verifica si el estado es activo
     */
    public function isActive(): bool
    {
        return match($this) {
            self::DELETED => false,
            self::CLOSE => true,
            self::EDITING_DRAFT => true,
            self::MERGED => true,
        };
    }

    /**
     * Verifica si el estado permite edición
     */
    public function isEditable(): bool
    {
        return match($this) {
            self::DELETED => false,
            self::CLOSE => false,
            self::EDITING_DRAFT => true,
            self::MERGED => false,
        };
    }

    public function isMerged(): bool
    {
        return match($this) {
            self::MERGED => true,
            default => false,
        };
    }

    public function isDeleted(): bool
    {
        return match($this) {
            self::DELETED => true,
            default => false,
        };
    }

    public function isClose(): bool
    {
        return match($this) {
            self::CLOSE => true,
            default => false,
        };
    }

    /**
     * Obtiene todos los estados disponibles
     */
    public static function cases(): array
    {
        return [
            self::DELETED,
            self::CLOSE,
            self::EDITING_DRAFT,
            self::MERGED,
        ];
    }

    /**
     * Obtiene el estado por su valor
     */
    public static function fromValue(int $value): ?self
    {
        return match($value) {
            0 => self::DELETED,
            1 => self::CLOSE,
            2 => self::EDITING_DRAFT,
            3 => self::MERGED,
            default => null,
        };
    }

    /**
     * Obtiene todos los estados activos
     */
    public static function active(): array
    {
        return array_filter(self::cases(), fn($status) => $status->isActive());
    }

    /**
     * Convierte el enum a array con información completa
     */
    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'name' => $this->name,
            'label' => $this->label(),
            'color' => $this->color(),
            'badge' => $this->badge(),
            'description' => $this->description(),
            'is_active' => $this->isActive(),
            'is_editable' => $this->isEditable(),
            'is_merged' => $this->isMerged(),
        ];
    }
}

