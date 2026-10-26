@extends('errors.layout')

@section('code', '403')
@section('title', 'You can’t do this here')
@section('message', $exception->getMessage() !== '' && $exception->getMessage() !== 'This action is unauthorized.'
    ? $exception->getMessage()
    : 'Your account doesn’t have access to this page. If you think it should, ask your SK Chairperson or the system administrator.')
