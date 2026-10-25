<?php

namespace App\Enums;

/**
 * Estados de pedidos y cotizaciones. Las transiciones permitidas se validan
 * en OrderService; aquí solo se definen los valores.
 */
enum OrderStatus: string
{
    // Pedido (purchase)
    case PendingPayment = 'pending_payment';
    case Paid = 'paid';
    case Shipped = 'shipped';
    case Delivered = 'delivered';

    // Cotización (quote)
    case New = 'new';
    case InReview = 'in_review';
    case Sent = 'sent';
    case Accepted = 'accepted';
    case Rejected = 'rejected';

    // Ambos
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PendingPayment => 'Pendiente de pago',
            self::Paid => 'Pagado',
            self::Shipped => 'Enviado',
            self::Delivered => 'Entregado',
            self::New => 'Nueva',
            self::InReview => 'En revisión',
            self::Sent => 'Enviada al cliente',
            self::Accepted => 'Aceptada',
            self::Rejected => 'Rechazada',
            self::Cancelled => 'Cancelado',
        };
    }

    /** Texto del botón que lleva a este estado ("Confirmar pago", "Cancelar"...). */
    public function actionLabel(): string
    {
        return match ($this) {
            self::PendingPayment => 'Volver a pendiente de pago',
            self::Paid => 'Confirmar pago',
            self::Shipped => 'Marcar como enviado',
            self::Delivered => 'Marcar como entregado',
            self::New => 'Volver a nueva',
            self::InReview => 'Iniciar revisión',
            self::Sent => 'Enviar al cliente',
            self::Accepted => 'Marcar como aceptada',
            self::Rejected => 'Marcar como rechazada',
            self::Cancelled => 'Cancelar',
        };
    }

    /**
     * Estado con el que nace cada tipo de solicitud.
     */
    public static function initialFor(OrderType $type): self
    {
        return match ($type) {
            OrderType::Purchase => self::PendingPayment,
            OrderType::Quote => self::New,
        };
    }
}
