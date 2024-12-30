<script>
import {Link, router} from '@inertiajs/vue3';
import {layoutComputed} from "@/store/helpers";
import simplebar from "simplebar-vue";

export default {
    components: {
        simplebar,
        Link
    },
    data() {
        return {
            settings: {
                minScrollbarLength: 60,
            },
        };
    },
    computed: {
        ...layoutComputed,
        layoutType: {
            get() {
                return this.$store ? this.$store.state.layout.layoutType : {} || {};
            },
        },
    },
    methods: {
        isActive(componentList) {
            return componentList.includes(this.$page.component);
        },
    },
};
</script>

<template>
    <BContainer fluid>
        <div id="two-column-menu"></div>

        <template v-if="layoutType === 'vertical' || layoutType === 'semibox'">
            <ul class="navbar-nav h-100" id="navbar-nav">
                <li class="menu-title">
                    <span data-key="global.menu"> {{ $t("global.menu") }}</span>
                </li>
                <li class="nav-item">
                    <Link :href="route('home')" :class="{active: $page.component === 'Dashboard/Index'}" class="nav-link menu-link">
                        <i class="fa fa-chart-line fs-base"></i> <span data-key="dashboard.title">{{ $t("dashboard.title") }}</span>
                    </Link>
                </li>
                
                <li class="nav-item">
                    <Link :href="route('pos.index')" :class="{active: $page.component === 'Pos/Index'}" class="nav-link menu-link">
                        <i class="fa fa-shopping-cart fs-base"></i> <span data-key="pos.title">{{ $t("pos.title") }}</span>
                    </Link>
                </li>

                <li class="menu-title">
                    <i class="ri-layout-grid-line"></i>
                    <span data-key="product.title">{{ $t("product.title") }}</span>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarPages" data-bs-toggle="collapse" role="button"
                       aria-expanded="false"
                       aria-controls="sidebarPages"
                    :class="{active: isActive(['Product/Index', 'Product/Create', 'AttributeGroup/Index'])}">
                        <i class="fa fa-cubes fs-base"></i>
                        <span data-key="product.title">{{ $t("product.title") }}</span>
                    </a>
                    <div class="collapse menu-dropdown" :class="{show: isActive(['Product/Index', 'Product/Create', 'AttributeGroup/Index'])}" id="sidebarPages">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <Link :href="route('product.index')" :class="{active: isActive(['Product/Index', 'Product/Create'])}" class="nav-link" data-key="product.title">{{ $t("product.title") }}</Link>
                            </li>
                            <li class="nav-item">
                                <Link :href="route('attribute-group.index')" :class="{active: $page.component === 'AttributeGroup/Index'}" class="nav-link" data-key="attribute-group.title">{{ $t("attribute-group.title") }}</Link>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="menu-title">
                    <i class="ri-layout-grid-line"></i>
                    <span data-key="global.management">{{ $t("global.management") }}</span>
                </li>

                <li class="nav-item">
                    <Link :href="route('customer.index')" :class="{active: $page.component === 'Customer/Index'}" class="nav-link menu-link">
                        <i class="fa fa-address-card fs-base"></i> <span data-key="customer.title">{{ $t("customer.title") }}</span>
                    </Link>
                </li>

                <li class="nav-item">
                    <Link :href="route('warehouse.index')" :class="{active: $page.component === 'Warehouse/Index'}" class="nav-link menu-link">
                        <i class="fa fa-warehouse fs-base"></i> <span data-key="warehouse.title">{{ $t("warehouse.title") }}</span>
                    </Link>
                </li>

                <li class="nav-item">
                    <Link :href="route('category.index')" :class="{active: $page.component === 'Category/Index'}" class="nav-link menu-link">
                        <i class="fa fa-tags fs-base"></i> <span data-key="category.title">{{ $t("category.title") }}</span>
                    </Link>
                </li>

                <li class="nav-item">
                    <Link :href="route('brand.index')" :class="{active: $page.component === 'Brand/Index'}" class="nav-link menu-link">
                        <i class="fa fa-business-time fs-base"></i> <span data-key="brand.title">{{ $t("brand.title") }}</span>
                    </Link>
                </li>
                <li class="nav-item">
                    <Link :href="route('vendor.index')" :class="{active: $page.component === 'Vendor/Index'}" class="nav-link menu-link">
                        <i class="fa fa-store fs-base"></i> <span data-key="vendor.title">{{ $t("vendor.title") }}</span>
                    </Link>
                </li>

                <li class="nav-item">
                    <Link :href="route('settings.index')" :class="{active: $page.component === 'Settings/Index'}" class="nav-link menu-link">
                        <i class="fa fa-cog fs-base"></i> <span data-key="settings.title">{{ $t("settings.title") }}</span>
                    </Link>
                </li>
            </ul>
        </template>
    </BContainer>
</template>
