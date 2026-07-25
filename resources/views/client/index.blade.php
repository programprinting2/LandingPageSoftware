<div id="dashClient" class="app-dashboard active">
    @include('client.components.sidebar')

    <div class="dash-main">
        @include('client.pages.overview')
        @include('client.pages.license')
        @include('client.pages.downloads')
        @include('client.pages.billing')
        @include('client.pages.settings')
    </div>
</div>
