<?php

namespace Botble\WooCommerceImporter\Http\Requests;

use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class WooCommerceImporterRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'site_url' => 'required',
            'consumer_key' => 'required',
            'consumer_sec' => 'required'
        ];
    }
}
