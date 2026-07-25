<div id="dashAffiliate" class="app-dashboard active">
    @include('affiliate.components.sidebar')

    <div class="dash-main">
        @include('affiliate.pages.overview')
        @include('affiliate.pages.referrals')
        @include('affiliate.pages.commissions')
        @include('affiliate.pages.settings')
    </div>
</div>
