@extends('layouts.app_admin')

@section('content')
    <section class="content-header">
        @component('blog.admin.components.breadcrumb')
            @slot('title') Panel @endslot
            @slot('parent') Main @endslot
            @slot('active') @endslot
        @endcomponent
    </section>
@endsection

