<?php

return [
    'required' => 'El campo :attribute es obligatorio.',
    'string' => 'El campo :attribute debe ser texto.',
    'numeric' => 'El campo :attribute debe ser numérico.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'email' => 'El correo electrónico no es válido.',
    'exists' => 'El valor seleccionado para :attribute no existe.',
    'unique' => 'El valor de :attribute ya se encuentra registrado.',
    'not_in' => 'La divisa seleccionada no está permitida.',
    'decimal' => 'El campo :attribute debe tener entre :min y :max decimales.',
    'min' => ['numeric' => 'El campo :attribute debe ser al menos :min.', 'string' => 'El campo :attribute debe tener al menos :min caracteres.'],
    'max' => ['string' => 'El campo :attribute no puede superar :max caracteres.'],
    'between' => ['numeric' => 'El campo :attribute debe estar entre :min y :max.'],
    'attributes' => [
        'name' => 'nombre',
        'description' => 'descripción',
        'price' => 'precio',
        'currency_id' => 'divisa',
        'tax_cost' => 'costo de impuestos',
        'manufacturing_cost' => 'costo de fabricación',
        'email' => 'correo electrónico',
        'password' => 'contraseña',
        'per_page' => 'cantidad por página',
    ],
];
