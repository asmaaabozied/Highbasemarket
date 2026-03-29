<?php

namespace App\Services;

use App\Jobs\DeleteCard;
use App\Models\Card;
use Illuminate\Support\Facades\Http;

class CardService
{
    public function getAccountCards(): \Illuminate\Database\Eloquent\Collection
    {
        return Card::query()->where('account_id', auth()->user()->getAccount()->id)
            ->get();
    }

    public function getAccountPaginateCards(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Card::query()->where('account_id', auth()->user()->getAccount()->id)
            ->paginate();
    }

    public function getTokenWithCardIdAndCustomerId($cardId, $customerId): mixed
    {
        $data = Http::withHeaders([
            'authorization' => 'Bearer '.config('auth.tap_secret_api_key'),
            'accept'        => 'application/json',
            'content-type'  => 'application/json',
        ])->post('https://api.tap.company/v2/tokens', [
            'saved_card' => [
                'card_id'     => $cardId,
                'customer_id' => $customerId,
            ],
        ]);

        return json_decode($data->body());
    }

    public function getCardToken()
    {

        $card = Card::query()->where('account_id', auth()->user()->getAccount()->id)->first();

        if (! $card) {
            return null;
        }
        $data = Http::withHeaders([
            'authorization' => 'Bearer '.config('auth.tap_secret_api_key'),
            'accept'        => 'application/json',
            'content-type'  => 'application/json',
        ])->post('https://api.tap.company/v2/tokens', [
            'saved_card' => [
                'card_id'     => $card->card_id,
                'customer_id' => $card->customer_id,
            ],
        ]);

        return json_decode($data->body());
    }

    public function saveCard($card, $customerId): void
    {
        $currentCard = Card::query()
            ->where('account_id', auth()->user()->getAccount()->id)
            ->where('last_four', $card->last_four)
            ->first();

        if ($currentCard) {
            return;
        }

        Card::query()->create([
            'card_id'     => $card->id,
            'object'      => $card->object,
            'first_six'   => $card->first_six,
            'first_eight' => $card->first_eight,
            'scheme'      => $card->scheme,
            'brand'       => $card->brand,
            'last_four'   => $card->last_four,
            'name'        => $card->name,
            'expiry'      => $card->expiry,
            'customer_id' => $customerId,
            'account_id'  => auth()->user()->getAccount()->id,
        ]);
    }

    public function deleteCard($id, $cardId, $customerId): void
    {
        Card::query()->findOrFail($id)->delete();

        DeleteCard::dispatch($cardId, $customerId);
    }
}
