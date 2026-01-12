<?php
/**
 * @package Yivic Base
 */
?>

@php
    try {
        $http_code = $exception->getStatusCode();
    } catch (\Exception $e) {
        $http_code = 500;
    }

    $errors = [
        '401' => __( 'Unauthorized', 'yivic-base' ),
        '403' => __( 'Forbidden', 'yivic-base' ),
        '404' => __( 'Not Found', 'yivic-base' ),
        '419' => __( 'Page Expired', 'yivic-base' ),
        '429' => __( 'Too Many Requests', 'yivic-base' ),
        '500' => __( 'Server Error', 'yivic-base' ),
        '503' => __( 'Service Unavailable', 'yivic-base' ),
    ];
    $error_message = !empty( $errors[$http_code] ) ? $errors[$http_code] : __( 'Error', 'yivic-base' );
@endphp

@extends('yivic-base::errors/layout-minimal-error')

@section('title', sprintf( __('WP App Error %s', 'yivic-base' ), $http_code ))
@section('code', $http_code)
@section('message', $error_message)