<?php

namespace App\Notifications;

use App\ExchangeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class ExchangeRequestNotification extends Notification
{
    use Queueable;

    protected $exchangeRequest;
    protected $action;

    /**
     * Create a new notification instance.
     */
    public function __construct(ExchangeRequest $exchangeRequest, $action)
    {
        $this->exchangeRequest = $exchangeRequest;
        $this->action = $action;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $message = new MailMessage;
        
        switch ($this->action) {
            case 'accepted':
                $message->subject('Exchange Request Accepted')
                    ->greeting('Hello ' . $notifiable->name . '!')
                    ->line('Your exchange request has been accepted by an FX Provider.')
                    ->line('Transaction Reference: ' . $this->exchangeRequest->transaction_reference)
                    ->line('Exchange: ' . $this->exchangeRequest->source_amount . ' ' . $this->exchangeRequest->source_currency . ' → ' . $this->exchangeRequest->destination_currency)
                    ->line('Please confirm your payment to proceed with the exchange.')
                    ->action('View Details', route('exchange-requests.show', $this->exchangeRequest->id));
                break;
                
            case 'rejected':
                $message->subject('Exchange Request Rejected')
                    ->greeting('Hello ' . $notifiable->name . '!')
                    ->line('Unfortunately, your exchange request has been rejected.')
                    ->line('Transaction Reference: ' . $this->exchangeRequest->transaction_reference)
                    ->line('Reason: ' . ($this->exchangeRequest->rejection_reason ?? 'Not specified'))
                    ->line('You can create a new exchange request at any time.');
                break;
                
            case 'user_payment_confirmed':
                $message->subject('User Payment Confirmed')
                    ->greeting('Hello!')
                    ->line('The user has confirmed their payment for the exchange request.')
                    ->line('Transaction Reference: ' . $this->exchangeRequest->transaction_reference)
                    ->line('Amount to Pay: ' . $this->exchangeRequest->destination_amount . ' ' . $this->exchangeRequest->destination_currency)
                    ->line('Account Holder: ' . $this->exchangeRequest->user->name)
                    ->line('Please complete the exchange by confirming your payment.')
                    ->action('Complete Exchange', route('exchange-requests.confirmation', $this->exchangeRequest->id));
                break;
                
            case 'completed':
                $message->subject('Exchange Completed Successfully')
                    ->greeting('Hello ' . $notifiable->name . '!')
                    ->line('Your currency exchange has been completed successfully!')
                    ->line('Transaction Reference: ' . $this->exchangeRequest->transaction_reference)
                    ->line('Exchanged: ' . $this->exchangeRequest->source_amount . ' ' . $this->exchangeRequest->source_currency . ' → ' . $this->exchangeRequest->destination_amount . ' ' . $this->exchangeRequest->destination_currency)
                    ->line('Thank you for using our exchange service.')
                    ->action('View Details', route('exchange-requests.show', $this->exchangeRequest->id));
                break;
        }
        
        return $message;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        $data = [
            'exchange_request_id' => $this->exchangeRequest->id,
            'transaction_reference' => $this->exchangeRequest->transaction_reference,
            'source_currency' => $this->exchangeRequest->source_currency,
            'destination_currency' => $this->exchangeRequest->destination_currency,
            'source_amount' => $this->exchangeRequest->source_amount,
            'destination_amount' => $this->exchangeRequest->destination_amount,
            'action' => $this->action,
        ];
        
        switch ($this->action) {
            case 'accepted':
                $data['title'] = 'Exchange Request Accepted';
                $data['message'] = 'Your exchange request has been accepted. Please confirm your payment.';
                break;
            case 'rejected':
                $data['title'] = 'Exchange Request Rejected';
                $data['message'] = 'Your exchange request has been rejected.';
                break;
            case 'user_payment_confirmed':
                $data['title'] = 'User Payment Confirmed';
                $data['message'] = 'User has confirmed payment. Please complete the exchange.';
                $data['account_holder'] = $this->exchangeRequest->user->name;
                break;
            case 'completed':
                $data['title'] = 'Exchange Completed';
                $data['message'] = 'Your currency exchange has been completed successfully!';
                break;
        }
        
        return $data;
    }
}
