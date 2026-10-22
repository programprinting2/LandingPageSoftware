<div id="dashClient" class="app-dashboard active{{ !empty($landingData['initialClient']) ? ' is-ready' : '' }}">
    @include('client.components.sidebar')

    <div class="dash-main">
        @include('client.pages.overview')
        @include('client.pages.license')
        @include('client.pages.downloads')
        @include('client.pages.billing')
        @include('client.pages.settings')
        @include('client.pages.affiliate-overview')
    </div>
</div>
