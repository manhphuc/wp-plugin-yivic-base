@php
	try {
		$http_code = $exception->getStatusCode();
	} catch (\Exception $e) {
		$http_code = 500;
	}

	$errors = [
		'401' => __( 'Unauthorized', 'yivic' ),
		'403' => __( 'Forbidden', 'yivic' ),
		'404' => __( 'Not Found', 'yivic' ),
		'419' => __( 'Page Expired', 'yivic' ),
		'429' => __( 'Too Many Requests', 'yivic' ),
		'500' => __( 'Server Error', 'yivic' ),
		'503' => __( 'Service Unavailable', 'yivic' ),
	];
	$error_message = !empty($errors[$http_code]) ? $errors[$http_code] : __('Error');
@endphp

@extends('yivic-base::errors/layout-minimal-error')

@section('title', sprintf(__('WP App Error %s', 'yivic' ), $http_code))
@section('code', $http_code)
@section('message', $error_message)
