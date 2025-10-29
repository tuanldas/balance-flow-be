@extends('layouts.coming-soon')

@section('content')
    <div class="flex flex-col items-center justify-center h-[95%]">
        <div class="mb-10">
            <img alt="image" class="dark:hidden max-h-[360px]" src="assets/media/illustrations/10.svg"/>
            <img alt="image" class="light:hidden max-h-[360px]" src="assets/media/illustrations/10-dark.svg"/>
        </div>
        <span class="kt-badge kt-badge-primary kt-badge-outline mb-3 text-xl p-5">
         Comming Soon
        </span>
    </div>
@endsection
