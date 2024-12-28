<script setup>
import {Link, router} from '@inertiajs/vue3';
import { onMounted, ref } from 'vue'
import { useStore } from 'vuex'
import axios from 'axios';

const store = useStore()

onMounted(() => {
    store.dispatch('auth/setLocale')
})

const logout = () => {
    router.post(route('logout'), {}, {
        preserveState: false,
        preserveScroll: false,
        onSuccess: () => {
            window.location.reload();
            alert('hi')
        }
    });
};

const searchQuery = ref('');
const searchResults = ref([]);
const isLoading = ref(false);

const searchProducts = async () => {
    if (searchQuery.value.length < 2) {
        searchResults.value = [];
        return;
    }

    isLoading.value = true;
    try {
        const response = await axios.get(route('search'), {
            params: {
                query: searchQuery.value
            }
        });
        searchResults.value = response.data;
    } catch (error) {
        console.error('Arama hatası:', error);
        searchResults.value = [];
    } finally {
        isLoading.value = false;
    }
};
</script>

<script>
import {Link} from '@inertiajs/vue3';
import simplebar from "simplebar-vue";

import i18n from "../i18n";

import us_flag from "@assets/images/flags/us.svg";
import tr_flag from "@assets/images/flags/tr.svg";

import img1 from "@assets/images/products/img-1.png";
import img2 from "@assets/images/products/img-2.png";
import img3 from "@assets/images/products/img-3.png";
import img4 from "@assets/images/products/img-4.png";
import img5 from "@assets/images/products/img-5.png";

import {layoutMethods} from "@/store/helpers";
import { changeLanguage } from "../i18n";

/**
 * Nav-bar Component
 */
export default {
    data() {
        return {
            languages: [
                {
                    flag: tr_flag,
                    language: "tr",
                    title: "Türkçe",
                },
                {
                    flag: us_flag,
                    language: "en",
                    title: "English",
                },
            ],
            cartItems: [
                {
                    id: 1,
                    productImage: img1,
                    productName: "Branded T-Shirts",
                    productLink: "/ecommerce/product-details",
                    quantity: "10 x $32",
                    itemPrice: "320",
                },
                {
                    id: 2,
                    productImage: img2,
                    productName: "Bentwood Chair",
                    productLink: "/ecommerce/product-details",
                    quantity: "5 x $18",
                    itemPrice: "89",
                },
                {
                    id: 3,
                    productImage: img3,
                    productName: "Borosil Paper Cup",
                    productLink: "/ecommerce/product-details",
                    quantity: "3 x $250",
                    itemPrice: "750",
                },
                {
                    id: 4,
                    productImage: img4,
                    productName: "Gray Styled T-Shirt",
                    productLink: "/ecommerce/product-details",
                    quantity: "1 x $1250",
                    itemPrice: "1250",
                },
                {
                    id: 5,
                    productImage: img5,
                    productName: "Stillbird Helmet",
                    productLink: "/ecommerce/product-details",
                    quantity: "2 x $495",
                    itemPrice: "990",
                },
            ],
            lan: i18n.locale,
            text: null,
            flag: null,
            value: null,
            myVar: 1,
        };
    },
    components: {
        simplebar,
        Link,
    },

    methods: {
        ...layoutMethods,
        isCustomDropdown() {
            //Search bar
            var searchOptions = document.getElementById("search-close-options");
            var dropdown = document.getElementById("search-dropdown");
            var searchInput = document.getElementById("search-options");

            searchInput.addEventListener("focus", () => {
                var inputLength = searchInput.value.length;
                if (inputLength > 0) {
                    dropdown.classList.add("show");
                    searchOptions.classList.remove("d-none");
                } else {
                    dropdown.classList.remove("show");
                    searchOptions.classList.add("d-none");
                }
            });

            searchInput.addEventListener("keyup", () => {
                var inputLength = searchInput.value.length;
                if (inputLength > 0) {
                    dropdown.classList.add("show");
                    searchOptions.classList.remove("d-none");
                } else {
                    dropdown.classList.remove("show");
                    searchOptions.classList.add("d-none");
                }
            });

            searchOptions.addEventListener("click", () => {
                searchInput.value = "";
                dropdown.classList.remove("show");
                searchOptions.classList.add("d-none");
            });

            document.body.addEventListener("click", (e) => {
                if (e.target.getAttribute("id") !== "search-options") {
                    dropdown.classList.remove("show");
                    searchOptions.classList.add("d-none");
                }
            });
        },
        toggleHamburgerMenu() {
            var windowSize = document.documentElement.clientWidth;
            let layoutType = document.documentElement.getAttribute("data-layout");

            document.documentElement.setAttribute("data-sidebar-visibility", "show");
            let visiblilityType = document.documentElement.getAttribute("data-sidebar-visibility");

            if (windowSize > 767)
                document.querySelector(".hamburger-icon").classList.toggle("open");

            //For collapse horizontal menu
            if (
                document.documentElement.getAttribute("data-layout") === "horizontal"
            ) {
                document.body.classList.contains("menu") ?
                    document.body.classList.remove("menu") :
                    document.body.classList.add("menu");
            }

            //For collapse vertical menu

            if (visiblilityType === "show" && (layoutType === "vertical" || layoutType === "semibox")) {
                if (windowSize < 1025 && windowSize > 767) {
                    document.body.classList.remove("vertical-sidebar-enable");
                    document.documentElement.getAttribute("data-sidebar-size") == "sm" ?
                        document.documentElement.setAttribute("data-sidebar-size", "") :
                        document.documentElement.setAttribute("data-sidebar-size", "sm");
                } else if (windowSize > 1025) {
                    document.body.classList.remove("vertical-sidebar-enable");
                    document.documentElement.getAttribute("data-sidebar-size") == "lg" ?
                        document.documentElement.setAttribute("data-sidebar-size", "sm") :
                        document.documentElement.setAttribute("data-sidebar-size", "lg");
                } else if (windowSize <= 767) {
                    document.body.classList.add("vertical-sidebar-enable");
                    document.documentElement.setAttribute("data-sidebar-size", "lg");
                }
            }

            //Two column menu
            if (document.documentElement.getAttribute("data-layout") === "twocolumn") {
                document.body.classList.contains("twocolumn-panel") ?
                    document.body.classList.remove("twocolumn-panel") :
                    document.body.classList.add("twocolumn-panel");
            }
        },
        toggleMenu() {
            this.$parent.toggleMenu();
        },
        toggleRightSidebar() {
            this.$parent.toggleRightSidebar();
        },
        initFullScreen() {
            document.body.classList.toggle("fullscreen-enable");
            if (
                !document.fullscreenElement &&
                /* alternative standard method */
                !document.mozFullScreenElement &&
                !document.webkitFullscreenElement
            ) {
                // current working methods
                if (document.documentElement.requestFullscreen) {
                    document.documentElement.requestFullscreen();
                } else if (document.documentElement.mozRequestFullScreen) {
                    document.documentElement.mozRequestFullScreen();
                } else if (document.documentElement.webkitRequestFullscreen) {
                    document.documentElement.webkitRequestFullscreen(
                        Element.ALLOW_KEYBOARD_INPUT
                    );
                }
            } else {
                if (document.cancelFullScreen) {
                    document.cancelFullScreen();
                } else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                } else if (document.webkitCancelFullScreen) {
                    document.webkitCancelFullScreen();
                }
            }
        },
        setLanguage(locale, country, flag) {
            this.lan = locale;
            this.text = country;
            this.flag = flag;
            document.getElementById("header-lang-img").setAttribute("src", flag);
            changeLanguage(locale)
            //i18n.global.locale = locale;
            //sessionStorage.setItem('locale', locale);
        },
        toggleDarkMode() {

            if (document.documentElement.getAttribute("data-bs-theme") == "dark") {
                document.documentElement.setAttribute("data-bs-theme", "light");
            } else {
                document.documentElement.setAttribute("data-bs-theme", "dark");
            }

            const mode = document.documentElement.getAttribute("data-bs-theme")
            this.changeMode({
                mode: mode,
            });
        },
        removeItem(cartItem) {
            this.cartItems = this.cartItems.filter(item => item.id !== cartItem.id)
            this.$emit("cart-item-price", this.cartItems.length);
        },
    },

    computed: {
        calculateTotalPrice() {
            return this.cartItems.reduce((total, item) => total + parseFloat(item.itemPrice), 0).toFixed(2);
        },
    },
    mounted() {
        this.flag = this.$i18n.locale;
        this.languages.forEach((item) => {
            if (item.language == this.flag) {
                document.getElementById("header-lang-img") ? document.getElementById("header-lang-img").setAttribute("src", item.flag) : '';
            }
        });

        document.addEventListener("scroll", function () {
            var pageTopbar = document.getElementById("page-topbar");
            if (pageTopbar) {
                document.body.scrollTop >= 50 || document.documentElement.scrollTop >= 50 ? pageTopbar.classList.add(
                    "topbar-shadow") : pageTopbar.classList.remove("topbar-shadow");
            }
        });
        if (document.getElementById("topnav-hamburger-icon"))
            document.getElementById("topnav-hamburger-icon").addEventListener("click", this.toggleHamburgerMenu);

        this.isCustomDropdown();
    },
};
</script>

<template>
    <header id="page-topbar">
        <div class="layout-width">
            <div class="navbar-header">
                <div class="d-flex">
                    <!-- LOGO -->
                    <div class="navbar-brand-box horizontal-logo">
                        <Link href="/" class="logo logo-dark">
            <span class="logo-sm">
              <img src="@assets/images/more-logo.png" alt="1" height="22"/>
            </span>
                            <span class="logo-lg">
              <img src="@assets/images/more-logo.png" alt="2" height="17"/>
            </span>
                        </Link>

                        <Link href="/" class="logo logo-light">
            <span class="logo-sm">
              <img src="@assets/images/logo-sm.png" alt="3" height="22"/>
            </span>
                            <span class="logo-lg">
              <img src="@assets/images/more-logo.png" alt="4" height="17"/>
            </span>
                        </Link>
                    </div>

                    <button type="button"
                            class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger shadow-none"
                            id="topnav-hamburger-icon">
            <span class="hamburger-icon">
              <span></span>
              <span></span>
              <span></span>
            </span>
                    </button>

                    <!-- App Search-->
                    <form class="app-search d-none d-md-block">
                        <div class="position-relative">
                            <input 
                                type="text" 
                                class="form-control" 
                                placeholder="Ürün ara..." 
                                autocomplete="off"
                                id="search-options" 
                                v-model="searchQuery"
                                @input="searchProducts"
                            />
                            <span class="mdi mdi-magnify search-widget-icon"></span>
                            <span 
                                class="mdi mdi-close-circle search-widget-icon search-widget-icon-close d-none"
                                id="search-close-options"
                            ></span>
                        </div>
                        
                        <!-- Arama sonuçları dropdown -->
                        <div class="dropdown-menu dropdown-menu-lg" id="search-dropdown" :class="{ show: searchResults.length > 0 }">
                            <simplebar data-simplebar style="max-height: 320px">
                                <div v-if="isLoading" class="p-3 text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Yükleniyor...</span>
                                    </div>
                                </div>

                                <div v-else-if="searchResults.length > 0">
                                    <div class="dropdown-header mt-2">
                                        <h6 class="text-overflow text-muted mb-2 text-uppercase">
                                            Ürünler
                                        </h6>
                                    </div>

                                    <div class="notification-list">
                                        <Link 
                                            v-for="product in searchResults" 
                                            :key="product.id"
                                            :href="route('product.show', product.id)"
                                            class="d-flex dropdown-item notify-item py-2"
                                        >
                                            <img 
                                                v-if="product.image"
                                                :src="product.image"
                                                class="me-3 rounded-circle avatar-xs"
                                                alt="ürün-resmi"
                                            />
                                            <div v-else class="me-3 rounded-circle avatar-xs bg-primary-subtle d-flex align-items-center justify-content-center">
                                                <i class="bx bx-package text-primary fs-16"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="m-0 text-truncate">{{ product.name }}</h6>
                                                <div class="d-flex align-items-center">
                                                    <span v-if="product.stock" class="fs-11 mb-0 text-success">
                                                        <i class="bx bx-check-circle me-1"></i>Stokta var
                                                    </span>
                                                    <span v-else class="fs-11 mb-0 text-danger">
                                                        <i class="bx bx-x-circle me-1"></i>Stokta yok
                                                    </span>
                                                </div>
                                            </div>
                                        </Link>
                                    </div>
                                </div>

                                <div v-else-if="searchQuery && !isLoading" class="p-3 text-center">
                                    <p class="mb-0">Sonuç bulunamadı</p>
                                </div>
                            </simplebar>
                        </div>
                    </form>
                </div>

                <div class="d-flex align-items-center">
                    <BDropdown class="dropdown d-md-none topbar-head-dropdown header-item" variant="ghost-secondary"
                               dropstart :offset="{ alignmentAxis: 55, crossAxis: 15, mainAxis: 0 }"
                               toggle-class="btn-icon btn-topbar rounded-circle arrow-none shadow-none"
                               menu-class="dropdown-menu-lg dropdown-menu-end p-0">
                        <template #button-content>
                            <i class="bx bx-search fs-22"></i>
                        </template>
                        <BDropdownItem aria-labelledby="page-header-search-dropdown">
                            <form class="p-3">
                                <div class="form-group m-0">
                                    <div class="input-group">
                                        <input type="text" class="form-control" placeholder="Search ..."
                                               aria-label="Recipient's username"/>
                                        <BButton variant="primary" type="submit">
                                            <i class="mdi mdi-magnify"></i>
                                        </BButton>
                                    </div>
                                </div>
                            </form>
                        </BDropdownItem>
                    </BDropdown>

                    <BDropdown class="dropdown" variant="ghost-secondary" dropstart
                               :offset="{ alignmentAxis: 55, crossAxis: 15, mainAxis: -50 }"
                               toggle-class="btn-icon btn-topbar rounded-circle arrow-none shadow-none"
                               menu-class="dropdown-menu-end">
                        <template #button-content><img id="header-lang-img" src="@assets/images/flags/us.svg"
                                                       alt="Header Language" height="20" class="rounded">
                        </template>
                        <BLink href="javascript:void(0);" class="dropdown-item notify-item language py-2"
                               v-for="(entry, key) in languages" :data-lang="entry.language" :title="entry.title"
                               @click="setLanguage(entry.language, entry.title, entry.flag)" :key="key">
                            <img :src="entry.flag" alt="user-image" class="me-2 rounded" height="18">
                            <span class="align-middle">{{ entry.title }}</span>
                        </BLink>
                    </BDropdown>

                    <BDropdown class="dropdown" variant="ghost-secondary" dropstart
                               :offset="{ alignmentAxis: 57, crossAxis: 0, mainAxis: -42 }"
                               toggle-class="btn-icon btn-topbar rounded-circle mode-layout ms-1 arrow-none shadow-none"
                               menu-class="p-0 dropdown-menu-end">
                        <template #button-content>
                            <i class="bx bx-category-alt fs-22"></i>
                        </template>
                        <div
                            class="p-3 border-top-0 border-start-0 border-end-0 border-dashed border dropdown-menu-lg">
                            <BRow class="align-items-center">
                                <BCol>
                                    <h6 class="m-0 fw-semibold fs-15">Web Apps</h6>
                                </BCol>
                                <BCol cols="auto">
                                    <BLink href="#!" class="btn btn-sm btn-soft-info shadow-none">
                                        View All Apps
                                        <i class="ri-arrow-right-s-line align-middle"></i>
                                    </BLink>
                                </BCol>
                            </BRow>
                        </div>

                        <div class="p-2">
                            <BRow class="g-0">
                                <BCol>
                                    <BLink class="dropdown-icon-item" href="#!">
                                        <img src="@assets/images/brands/github.png" alt="Github"/>
                                        <span>GitHub</span>
                                    </BLink>
                                </BCol>
                                <BCol>
                                    <BLink class="dropdown-icon-item" href="#!">
                                        <img src="@assets/images/brands/bitbucket.png" alt="bitbucket"/>
                                        <span>Bitbucket</span>
                                    </BLink>
                                </BCol>
                                <BCol>
                                    <BLink class="dropdown-icon-item" href="#!">
                                        <img src="@assets/images/brands/dribbble.png" alt="dribbble"/>
                                        <span>Dribbble</span>
                                    </BLink>
                                </BCol>
                            </BRow>

                            <BRow class="g-0">
                                <BCol>
                                    <BLink class="dropdown-icon-item" href="#!">
                                        <img src="@assets/images/brands/dropbox.png" alt="dropbox"/>
                                        <span>Dropbox</span>
                                    </BLink>
                                </BCol>
                                <BCol>
                                    <BLink class="dropdown-icon-item" href="#!">
                                        <img src="@assets/images/brands/mail_chimp.png" alt="mail_chimp"/>
                                        <span>Mail Chimp</span>
                                    </BLink>
                                </BCol>
                                <BCol>
                                    <BLink class="dropdown-icon-item" href="#!">
                                        <img src="@assets/images/brands/slack.png" alt="slack"/>
                                        <span>Slack</span>
                                    </BLink>
                                </BCol>
                            </BRow>
                        </div>
                    </BDropdown>

                    <BDropdown variant="ghost-secondary" dropstart
                               :offset="{ alignmentAxis: 57, crossAxis: 0, mainAxis: -42 }" class="ms-1 dropdown"
                               toggle-class="btn-icon btn-topbar rounded-circle mode-layout arrow-none shadow-none"
                               menu-class="dropdown-menu-xl dropdown-menu-end p-0"
                               text="Manual close (auto-close=false)" auto-close="outside">
                        <template #button-content>
                            <i class="bx bx-shopping-bag fs-22"></i>
                            <span
                                class="position-absolute topbar-badge cartitem-badge fs-10 translate-middle badge rounded-pill bg-info">{{
                                    cartItems.length
                                }} </span>
                        </template>

                        <div
                            class="p-3 border-top-0 border-start-0 dropdown-head border-end-0 border-dashed border dropdown-menu-xl">
                            <BRow class="align-items-center">
                                <BCol>
                                    <h6 class="m-0 fs-16 fw-semibold"> My Cart</h6>
                                </BCol>
                                <BCol cols="auto">
                                    <BBadge variant="warning-subtle" class="bg-warning-subtle text-warning fs-13"><span
                                        class="cartitem-badge"> {{ cartItems.length }} </span>
                                        items
                                    </BBadge>
                                </BCol>
                            </BRow>
                        </div>
                        <simplebar data-simplebar style="max-height: 300px">
                            <div class="p-2">
                                <div class="text-center empty-cart" id="empty-cart" v-if="cartItems.length === 0">
                                    <div class="avatar-md mx-auto my-3">
                                        <div class="avatar-title bg-info-subtle text-info fs-36 rounded-circle">
                                            <i class="bx bx-cart"></i>
                                        </div>
                                    </div>
                                    <h5 class="mb-3">Your Cart is Empty!</h5>
                                    <Link href="/ecommerce/products" class="btn btn-success w-md mb-3">Shop Now</Link>
                                </div>
                                <div class="d-block dropdown-item dropdown-item-cart text-wrap px-3 py-2"
                                     v-for="(item, index) in cartItems" :key="index">
                                    <div class="d-flex align-items-center">
                                        <img :src="item.productImage"
                                             class="me-3 rounded-circle avatar-sm p-2 bg-light"/>
                                        <div class="flex-grow-1">
                                            <h6 class="mt-0 mb-1 fs-14">
                                                <Link :href="item.productLink" class="text-reset">{{
                                                        item.productName
                                                    }}
                                                </Link>
                                            </h6>
                                            <p class="mb-0 fs-12 text-muted">
                                                Quantity: <span>{{ item.quantity }}</span>
                                            </p>
                                        </div>
                                        <div class="px-2">
                                            <h5 class="m-0 fw-normal">$<span class="cart-item-price">{{
                                                    item.itemPrice
                                                }}</span></h5>
                                        </div>
                                        <div class="ps-2">
                                            <button type="button"
                                                    class="btn btn-ghost-secondary btn-sm btn-icon remove-item-btn shadow-none"
                                                    @click="removeItem(item)">
                                                <i class="ri-close-fill fs-16"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </simplebar>
                        <div v-if="cartItems.length"
                             class="p-3 border-bottom-0 border-start-0 border-end-0 border-dashed border"
                             id="checkout-elem">
                            <div class="d-flex justify-content-between align-items-center pb-3">
                                <h5 class="m-0 text-muted">Total:</h5>
                                <div class="px-2">
                                    <h5 class="m-0" id="cart-item-total">${{ calculateTotalPrice }}</h5>
                                </div>
                            </div>

                            <Link href="/ecommerce/checkout" class="btn btn-success text-center w-100">
                                Checkout
                            </Link>
                        </div>
                    </BDropdown>

                    <div class="ms-1 header-item d-none d-sm-flex">
                        <BButton type="button" variant="ghost-secondary"
                                 class="btn-icon btn-topbar rounded-circle shadow-none" data-toggle="fullscreen"
                                 @click="initFullScreen">
                            <i class="bx bx-fullscreen fs-22"></i>
                        </BButton>
                    </div>

                    <div class="ms-1 header-item d-none d-sm-flex">
                        <BButton type="button" variant="ghost-secondary"
                                 class="btn-icon btn-topbar rounded-circle light-dark-mode shadow-none"
                                 @click="toggleDarkMode">
                            <i class="bx bx-moon fs-22"></i>
                        </BButton>
                    </div>

                    <BDropdown variant="ghost-dark" dropstart class="ms-1 dropdown"
                               :offset="{ alignmentAxis: 57, crossAxis: 0, mainAxis: -42 }"
                               toggle-class="btn-icon btn-topbar rounded-circle arrow-none shadow-none"
                               id="page-header-notifications-dropdown"
                               menu-class="dropdown-menu-lg dropdown-menu-end p-0" auto-close="outside">
                        <template #button-content>
                            <i class='bx bx-bell fs-22'></i>
                            <span
                                class="position-absolute topbar-badge fs-10 translate-middle badge rounded-pill bg-danger"><span
                                class="notification-badge">3</span><span class="visually-hidden">unread
                  messages
                </span>
              </span>
                        </template>
                        <div class="dropdown-head bg-primary bg-pattern rounded-top dropdown-menu-lg">
                            <div class="p-3">
                                <BRow class="align-items-center">
                                    <BCol>
                                        <h6 class="m-0 fs-16 fw-semibold text-white">
                                            Notifications
                                        </h6>
                                    </BCol>
                                    <BCol cols="auto" class="dropdown-tabs">
                                        <BBadge variant="light-subtle" class="bg-light-subtle text-body fs-13"> 4 New
                                        </BBadge>
                                    </BCol>
                                </BRow>
                            </div>
                        </div>
                        <BTabs nav-class="dropdown-tabs nav-tab-custom bg-primary px-2 pt-2">
                            <BTab title=" All (4) " class="tab-pane fade py-2 ps-2 show" id="all-noti-tab"
                                  role="tabpanel">
                                <simplebar data-simplebar style="max-height: 300px" class="pe-2">
                                    <div class="text-reset notification-item d-block dropdown-item position-relative">
                                        <div class="d-flex">
                                            <div class="avatar-xs me-3 flex-shrink-0">
                        <span class="avatar-title bg-info-subtle text-info rounded-circle fs-16">
                          <i class="bx bx-badge-check"></i>
                        </span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <BLink href="#!" class="stretched-link">
                                                    <h6 class="mt-0 mb-2 lh-base">
                                                        Your <b>Elite</b> author Graphic Optimization
                                                        <span class="text-secondary">reward</span> is
                                                        ready!
                                                    </h6>
                                                </BLink>
                                                <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                                    <span><i class="mdi mdi-clock-outline"></i> Just 30 sec ago</span>
                                                </p>
                                            </div>
                                            <div class="px-2 fs-15">
                                                <input class="form-check-input" type="checkbox"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-reset notification-item d-block dropdown-item position-relative">
                                        <div class="d-flex">
                                            <img src="@assets/images/users/avatar-2.jpg"
                                                 class="me-3 rounded-circle avatar-xs flex-shrink-0" alt="user-pic"/>
                                            <div class="flex-grow-1">
                                                <BLink href="#!" class="stretched-link">
                                                    <h6 class="mt-0 mb-1 fs-13 fw-semibold">
                                                        Angela Bernier
                                                    </h6>
                                                </BLink>
                                                <div class="fs-13 text-muted">
                                                    <p class="mb-1">
                                                        Answered to your comment on the cash flow forecast's graph 🔔.
                                                    </p>
                                                </div>
                                                <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                                    <span><i class="mdi mdi-clock-outline"></i> 48 min ago</span>
                                                </p>
                                            </div>
                                            <div class="px-2 fs-15">
                                                <input class="form-check-input" type="checkbox"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-reset notification-item d-block dropdown-item position-relative">
                                        <div class="d-flex">
                                            <div class="avatar-xs me-3 flex-shrink-0">
                        <span class="avatar-title bg-danger-subtle text-danger rounded-circle fs-16">
                          <i class="bx bx-message-square-dots"></i>
                        </span>
                                            </div>
                                            <div class="flex-grow-1">
                                                <BLink href="#!" class="stretched-link">
                                                    <h6 class="mt-0 mb-2 fs-13 lh-base">
                                                        You have received <b class="text-success">20</b> new messages in
                                                        the conversation
                                                    </h6>
                                                </BLink>
                                                <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                          <span><i class="mdi mdi-clock-outline"></i> 2 hrs
                            ago</span>
                                                </p>
                                            </div>
                                            <div class="px-2 fs-15">
                                                <input class="form-check-input" type="checkbox"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-reset notification-item d-block dropdown-item position-relative">
                                        <div class="d-flex">
                                            <img src="@assets/images/users/avatar-8.jpg"
                                                 class="me-3 rounded-circle avatar-xs flex-shrink-0" alt="user-pic"/>
                                            <div class="flex-grow-1">
                                                <BLink href="#!" class="stretched-link">
                                                    <h6 class="mt-0 mb-1 fs-13 fw-semibold">
                                                        Maureen Gibson
                                                    </h6>
                                                </BLink>
                                                <div class="fs-13 text-muted">
                                                    <p class="mb-1">
                                                        We talked about a project on linkedin.
                                                    </p>
                                                </div>
                                                <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                          <span><i class="mdi mdi-clock-outline"></i> 4 hrs
                            ago</span>
                                                </p>
                                            </div>
                                            <div class="px-2 fs-15">
                                                <input class="form-check-input" type="checkbox"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="my-3 text-center">
                                        <BButton type="button" variant="soft-success">
                                            View All Notifications
                                            <i class="ri-arrow-right-line align-middle"></i>
                                        </BButton>
                                    </div>
                                </simplebar>
                            </BTab>

                            <BTab title="Messages" class="tab-pane fade py-2 ps-2" id="messages-tab" role="tabpanel"
                                  aria-labelledby="messages-tab">
                                <simplebar data-simplebar style="max-height: 300px" class="pe-2">
                                    <div class="text-reset notification-item d-block dropdown-item">
                                        <div class="d-flex">
                                            <img src="@assets/images/users/avatar-3.jpg"
                                                 class="me-3 rounded-circle avatar-xs" alt="user-pic"/>
                                            <div class="flex-grow-1">
                                                <BLink href="#!" class="stretched-link">
                                                    <h6 class="mt-0 mb-1 fs-13 fw-semibold">
                                                        James Lemire
                                                    </h6>
                                                </BLink>
                                                <div class="fs-13 text-muted">
                                                    <p class="mb-1">
                                                        We talked about a project on linkedin.
                                                    </p>
                                                </div>
                                                <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                                    <span><i class="mdi mdi-clock-outline"></i> 30 min ago</span>
                                                </p>
                                            </div>
                                            <div class="px-2 fs-15">
                                                <input class="form-check-input" type="checkbox"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-reset notification-item d-block dropdown-item">
                                        <div class="d-flex">
                                            <img src="@assets/images/users/avatar-2.jpg"
                                                 class="me-3 rounded-circle avatar-xs" alt="user-pic"/>
                                            <div class="flex-grow-1">
                                                <BLink href="#!" class="stretched-link">
                                                    <h6 class="mt-0 mb-1 fs-13 fw-semibold">
                                                        Angela Bernier
                                                    </h6>
                                                </BLink>
                                                <div class="fs-13 text-muted">
                                                    <p class="mb-1">
                                                        Answered to your comment on the cash flow
                                                        forecast's graph 🔔.
                                                    </p>
                                                </div>
                                                <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                          <span><i class="mdi mdi-clock-outline"></i> 2 hrs
                            ago</span>
                                                </p>
                                            </div>
                                            <div class="px-2 fs-15">
                                                <input class="form-check-input" type="checkbox"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-reset notification-item d-block dropdown-item">
                                        <div class="d-flex">
                                            <img src="@assets/images/users/avatar-6.jpg"
                                                 class="me-3 rounded-circle avatar-xs" alt="user-pic"/>
                                            <div class="flex-grow-1">
                                                <BLink href="#!" class="stretched-link">
                                                    <h6 class="mt-0 mb-1 fs-13 fw-semibold">
                                                        Kenneth Brown
                                                    </h6>
                                                </BLink>
                                                <div class="fs-13 text-muted">
                                                    <p class="mb-1">
                                                        Mentionned you in his comment on 📃 invoice
                                                        #12501.
                                                    </p>
                                                </div>
                                                <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                          <span><i class="mdi mdi-clock-outline"></i> 10 hrs
                            ago</span>
                                                </p>
                                            </div>
                                            <div class="px-2 fs-15">
                                                <input class="form-check-input" type="checkbox"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-reset notification-item d-block dropdown-item">
                                        <div class="d-flex">
                                            <img src="@assets/images/users/avatar-8.jpg"
                                                 class="me-3 rounded-circle avatar-xs" alt="user-pic"/>
                                            <div class="flex-grow-1">
                                                <BLink href="#!" class="stretched-link">
                                                    <h6 class="mt-0 mb-1 fs-13 fw-semibold">
                                                        Maureen Gibson
                                                    </h6>
                                                </BLink>
                                                <div class="fs-13 text-muted">
                                                    <p class="mb-1">
                                                        We talked about a project on linkedin.
                                                    </p>
                                                </div>
                                                <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                          <span><i class="mdi mdi-clock-outline"></i> 3 days
                            ago</span>
                                                </p>
                                            </div>
                                            <div class="px-2 fs-15">
                                                <input class="form-check-input" type="checkbox"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="my-3 text-center">
                                        <BButton type="button" variant="soft-success">
                                            View All Messages
                                            <i class="ri-arrow-right-line align-middle"></i>
                                        </BButton>
                                    </div>
                                </simplebar>
                            </BTab>

                            <BTab title="Alerts" class="p-4">
                                <simplebar data-simplebar style="max-height: 300px" class="pe-2">
                                    <div class="w-25 w-sm-50 pt-3 mx-auto">
                                        <img src="@assets/images/svg/bell.svg" class="img-fluid" alt="user-pic"/>
                                    </div>
                                    <div class="text-center pb-5 mt-2">
                                        <h6 class="fs-18 fw-semibold lh-base">
                                            Hey! You have no any notifications
                                        </h6>
                                    </div>
                                </simplebar>
                            </BTab>
                        </BTabs>
                    </BDropdown>

                    <BDropdown variant="link" class="ms-sm-3 header-item topbar-user"
                               toggle-class="rounded-circle arrow-none shadow-none" menu-class="dropdown-menu-end"
                               :offset="{ alignmentAxis: -14, crossAxis: 0, mainAxis: 0 }">
                        <template #button-content>
              <span class="d-flex align-items-center">
                <img v-if="$page.props.jetstream.managesProfilePhotos" class="rounded-circle header-profile-user"
                     :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                <span class="text-start ms-xl-2">
                  <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">{{ $page.props.auth.user.name }}</span>
                  <span class="d-none d-xl-block ms-1 fs-12 user-name-sub-text">{{ $page.props.tenant.name }}</span>
                </span>
              </span>
                        </template>
                        <h6 class="dropdown-header">Welcome {{ $page.props.auth.user.name }}!</h6>
                        <Link class="dropdown-item" :href="route('profile.show')"><i
                            class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i>
                            <span class="align-middle">Profile</span>
                        </Link>
                        <Link class="dropdown-item" v-if="$page.props.jetstream.hasApiFeatures"
                              :href="route('api-tokens.index')"><i
                            class="mdi mdi-key-variant text-muted fs-16 align-middle me-1"></i>
                            <span class="align-middle"> API Tokens</span>
                        </Link>
                        <div class="dropdown-divider"></div>
                        <Link class="dropdown-item" href="#">
                            <i class=" mdi mdi-message-text-outline text-muted fs-16 align-middle me-1"></i>
                            <span class="align-middle"> Messages</span>
                        </Link>
                        <Link class="dropdown-item" href="#">
                            <i class="mdi mdi-calendar-check-outline text-muted fs-16 align-middle me-1"></i>
                            <span class="align-middle"> Taskboard</span>
                        </Link>
                        <Link class="dropdown-item" href="#"><i
                            class="mdi mdi-lifebuoy text-muted fs-16 align-middle me-1"></i>
                            <span class="align-middle"> Help</span>
                        </Link>
                        <div class="dropdown-divider"></div>
                        <Link class="dropdown-item" href="#"><i
                            class="mdi mdi-wallet text-muted fs-16 align-middle me-1"></i>
                            <span class="align-middle"> Balance : <b>$5971.67</b></span>
                        </Link>
                        <Link class="dropdown-item" href="#">
                            <BBadge variant="success-subtle" class="bg-success-subtle text-success mt-1 float-end">New
                            </BBadge>
                            <i class="mdi mdi-cog-outline text-muted fs-16 align-middle me-1"></i>
                            <span class="align-middle"> Settings</span>
                        </Link>
                        <Link class="dropdown-item" href="/auth/lockscreen-basic"><i
                            class="mdi mdi-lock text-muted fs-16 align-middle me-1"></i>
                            <span class="align-middle"> Lock screen</span>
                        </Link>

                        <!-- Authentication -->
                        <form method="POST" @submit.prevent="logout" class="dropdown-item">
                            <BButton variant="none" type="submit" class="p-0 shadow-none"><i
                                class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i> Logout
                            </BButton>
                        </form>
                    </BDropdown>
                </div>
            </div>
        </div>
    </header>
</template>
