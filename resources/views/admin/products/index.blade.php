@extends('layouts.admin.layout')
@section('content')
    <product-component
        :form-add="{{ json_encode(route('admin.product.create')) }}"
    ></product-component>
@endsection
