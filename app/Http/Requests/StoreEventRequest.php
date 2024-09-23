<?php

namespace App\Http\Requests;

use App\Models\Event;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate as FacadesGate;
use Symfony\Component\HttpFoundation\Response;

class StoreEventRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(FacadesGate::denies('event_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;

    }

    public function rules()
    {
        return [
            'room_id'    => [
                'required',
                'integer'],
            'user_id'    => [
                'required',
                'integer'],
            'title'      => [
                'required'],
            'start_time' => [
                'required',
                'date_format:Y-m-d',
            ],
            'end_time'   => [
                'required',
                'date_format:Y-m-d'
            ]
        ];

    }
}
