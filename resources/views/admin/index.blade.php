<div id="dashAdmin" class="app-dashboard active">
    @include('admin.components.sidebar')

    <div class="dash-main">
        @include('admin.pages.overview')
        @include('admin.pages.clients')
        @include('admin.pages.applications')
        @include('admin.pages.licenses')
        @include('admin.pages.affiliates')
        @include('admin.pages.promo-vouchers')
        @include('admin.pages.commissions')
        @include('admin.pages.transactions')
        @include('admin.pages.homepage')
        @include('admin.pages.landing-cms')
        @include('admin.pages.revenue')
        @include('admin.pages.settings')
    </div>
</div>
