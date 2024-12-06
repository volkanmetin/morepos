<script>
localStorage.setItem("rightbar_isopen", false);
import {layoutMethods, layoutComputed} from "@/store/helpers";
import simplebar from "simplebar-vue";

/**
 * Right sidebar component
 */

export default {
    data() {
        return {
            show: false,
            showGradients: false,
            resetLayoutMode: {},
        };
    },
    beforeCreate() {
        localStorage.setItem("resetValue", JSON.stringify(this.$store.state.layout));
    },
    methods: {
        ...layoutMethods,
        click() {
            this.show = !this.show;
        },
        topFunction() {
            document.body.scrollTop = 0;
            document.documentElement.scrollTop = 0;
        },

        resizeWindow() {
            var windowSize = document.documentElement.clientWidth;
            if (windowSize >= 1025) {
                if (document.documentElement.getAttribute("data-layout") === "vertical") {
                    document.documentElement.setAttribute("data-sidebar-size", this.$store.state.layout.sidebarSize);
                }
                if (document.documentElement.getAttribute("data-layout") === "semibox") {
                    document.documentElement.setAttribute("data-sidebar-size", this.$store.state.layout.sidebarSize);
                }
                if (document.documentElement.getAttribute("data-sidebar-visibility") === "show" && document.querySelector(".hamburger-icon")) {
                    document.querySelector(".hamburger-icon").classList.remove("open");
                }
            } else if (windowSize < 1025 && windowSize > 767) {
                document.body.classList.remove("twocolumn-panel");
                if (document.documentElement.getAttribute("data-layout") === "vertical") {
                    document.documentElement.setAttribute("data-sidebar-size", "sm");
                }
                if (document.documentElement.getAttribute("data-layout") === "semibox") {
                    document.documentElement.setAttribute("data-sidebar-size", "sm");
                }
                if (document.querySelector(".hamburger-icon")) {
                    document.querySelector(".hamburger-icon").classList.add("open");
                }
            } else if (windowSize <= 767) {
                document.body.classList.remove("vertical-sidebar-enable");
                document.body.classList.add("twocolumn-panel");
                if (document.documentElement.getAttribute("data-layout") !== "horizontal") {
                    document.documentElement.setAttribute("data-sidebar-size", "lg");
                }
                if (document.querySelector(".hamburger-icon")) {
                    document.querySelector(".hamburger-icon").classList.add("open");
                }
            }
        },

        resetLayout() {
            let reset = JSON.parse(localStorage.getItem("resetValue"));
            document.documentElement.setAttribute("data-sidebar-size", "lg");
            this.changeMode({mode: reset.mode});
            this.changeSidebarColor({sidebarColor: reset.sidebarColor});
            this.changeLayoutType({layoutType: reset.layoutType});
            this.changeTopbar({topbar: reset.topbar});
            this.changeLayoutWidth({layoutWidth: reset.layoutWidth});
            this.changeSidebarSize({sidebarSize: reset.sidebarSize});
            this.changeSidebarImage({sidebarImage: reset.sidebarImage});
            this.changeSidebarColor({sidebarColor: reset.sidebarColor});
            this.changePreloader({preloader: reset.preloader});
            this.changeSidebarView({sidebarView: reset.sidebarView});
            this.changeVisibility({visibility: reset.visibility});
            this.changePosition({position: reset.position});
        },

        gradiantColor() {
            this.changeSidebarColor({sidebarColor: "gradient"})
        },

        onSideBarColorClick(color) {
            if (color !== 'gradient') {
                this.showGradients = false
            } else {
                this.showGradients = true
                this.gradiantColor();
            }
        }
    },
    mounted() {
        let backtoTop = document.getElementById("back-to-top");

        if (backtoTop) {
            backtoTop = document.getElementById("back-to-top");
            window.onscroll = function () {
                if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
                    backtoTop.style.display = "block";
                } else {
                    backtoTop.style.display = "none";
                }
            };
        }
        var setpreloader = document.getElementById("preloader");
        if (localStorage.getItem('data-preloader') && localStorage.getItem('data-preloader') == 'enable') {
            document.documentElement.setAttribute("data-preloader", "enable");
            if (setpreloader) {
                setTimeout(function () {
                    setpreloader.style.opacity = "0";
                    setpreloader.style.visibility = "hidden";
                }, 1000);
            }
        } else {
            document.documentElement.setAttribute("data-preloader", "disable");
            if (setpreloader) {
                setpreloader.style.opacity = "0";
                setpreloader.style.visibility = "hidden";
            }
        }
        if (document.getElementById('collapseBgGradient')) {
            Array.from(document.querySelectorAll("#collapseBgGradient .form-check input")).forEach(function () {
                if (document.querySelector("[data-bs-target='#collapseBgGradient']")) {
                    document.querySelector("[data-bs-target='#collapseBgGradient']").addEventListener('click', function () {
                        document.getElementById("sidebar-color-gradient").click();
                    });
                }
            });
            Array.from(document.querySelectorAll("[name='data-sidebar']")).forEach(function (elem) {
                if (document.querySelector("[data-bs-target='#collapseBgGradient']")) {
                    if (document.querySelector("#collapseBgGradient .form-check input:checked")) {
                        document.querySelector("[data-bs-target='#collapseBgGradient']").classList.add("active");
                    } else {
                        document.querySelector("[data-bs-target='#collapseBgGradient']").classList.remove("active");
                        document.getElementById('collapseBgGradient').classList.remove('show');
                    }

                    elem.addEventListener("change", function () {
                        if (document.querySelector("#collapseBgGradient .form-check input:checked")) {
                            document.querySelector("[data-bs-target='#collapseBgGradient']").classList.add("active");
                        } else {
                            document.getElementById('collapseBgGradient').classList.remove('show');
                            document.querySelector("[data-bs-target='#collapseBgGradient']").classList.remove("active");
                        }
                    });
                }
            });
        }

        window.addEventListener("resize", this.resizeWindow);
    },
    computed: {
        ...layoutComputed,
        layoutType: {
            get() {
                return this.$store ? this.$store.state.layout.layoutType : {} || {};
            },
            set(layout) {
                localStorage.setItem("rightbar_isopen", true);
                this.changeLayoutType({layoutType: layout,});
                document.querySelector(".hamburger-icon").classList.remove("open");
            },
        },
        preloader: {
            get() {
                return this.$store ? this.$store.state.layout.preloader : {} || {};
            },
            set(preloader) {
                return this.changePreloader({
                    preloader: preloader,
                });
            },
        },
        mode: {
            get() {
                return this.$store ? this.$store.state.layout.mode : {} || {};
            },
            set(mode) {
                if (mode == "dark") {
                    this.changeMode({mode: mode});
                    this.changeTopbar({topbar: "light"});
                } else {
                    this.changeMode({mode: mode});
                    this.changeTopbar({topbar: "light"});
                }
            },
        },
        sidebarSize: {
            get() {
                return this.$store ? this.$store.state.layout.sidebarSize : {} || {};
            },
            set(type) {
                return this.changeSidebarSize({
                    sidebarSize: type,
                });
            },
        },
        layoutWidth: {
            get() {
                return this.$store ? this.$store.state.layout.layoutWidth : {} || {};
            },
            set(width) {
                if (width == 'boxed') {
                    this.changeLayoutWidth({layoutWidth: width});
                    this.changeSidebarSize({sidebarSize: 'sm-hover'});
                } else {
                    this.changeLayoutWidth({layoutWidth: width});
                    this.changeSidebarSize({sidebarSize: 'lg'});
                }
            },
        },
        position: {
            get() {
                return this.$store ? this.$store.state.layout.position : {} || {};
            },
            set(position) {
                return this.changePosition({
                    position: position,
                });
            },
        },
        topbar: {
            get() {
                return this.$store ? this.$store.state.layout.topbar : {} || {};
            },
            set(topbar) {
                this.changeTopbar({
                    topbar: topbar,
                });
            },
        },
        sidebarView: {
            get() {
                return this.$store ? this.$store.state.layout.sidebarView : {} || {};
            },
            set(sidebarView) {
                return this.changeSidebarView({
                    sidebarView: sidebarView,
                });
            },
        },
        sidebarColor: {
            get() {
                return this.$store ? this.$store.state.layout.sidebarColor : {} || {};
            },
            set(sidebarColor) {
                return this.changeSidebarColor({
                    sidebarColor: sidebarColor,
                });
            },
        },
        sidebarImage: {
            get() {
                return this.$store ? this.$store.state.layout.sidebarImage : {} || {};
            },
            set(sidebarImage) {
                return this.changeSidebarImage({
                    sidebarImage: sidebarImage,
                });
            },
        },

        visibility: {
            get() {
                return this.$store ? this.$store.state.layout.visibility : {} || {};
            },
            set(visibility) {
                if (visibility == 'hidden') {
                    document.querySelector(".hamburger-icon").classList.add("open");
                } else {
                    document.querySelector(".hamburger-icon").classList.remove("open");
                }
                this.changeVisibility({visibility: visibility});
            },
        },
    },

    watch: {
        show: {
            immediate: true,
            deep: true,
            handler(newVal, oldVal) {
                if (newVal !== oldVal) {
                    if (!newVal) {
                        document.body.removeAttribute("style");
                    }
                    // else {
                    //   setTimeout(() => {
                    //     document.body.setAttribute("style", "overflow: hidden; padding-right:17px");
                    //   }, 500)
                    // }
                }
            },
        },
        mode: {
            immediate: true,
            deep: true,
            handler(newVal, oldVal) {
                if (newVal !== oldVal) {
                    switch (newVal) {
                        case "dark":
                            document.documentElement.setAttribute("data-bs-theme", "dark");
                            break;
                        case "light":
                            document.documentElement.setAttribute("data-bs-theme", "light");
                            break;
                    }
                }
            },
        },
        preloader: {
            immediate: true,
            deep: true,
            handler(newVal, oldVal) {
                if (newVal !== oldVal) {
                    switch (newVal) {
                        case "enable":
                            document.documentElement.setAttribute("data-preloader", "enable");
                            break;
                        case "disable":
                            document.documentElement.setAttribute("data-preloader", "disable");
                            break;
                    }
                    localStorage.setItem('data-preloader', newVal);
                }
            },
        },
        layoutType: {
            immediate: true,
            deep: true,
            handler(newVal, oldVal) {
                if (newVal !== oldVal) {
                    switch (newVal) {
                        case "horizontal":
                            document.documentElement.setAttribute("data-layout", "horizontal");
                            break;
                        case "vertical":
                            document.documentElement.setAttribute("data-layout", "vertical");
                            break;
                        case "twocolumn":
                            document.documentElement.setAttribute("data-layout", "twocolumn");
                            break;
                        case "semibox":
                            document.documentElement.setAttribute("data-layout", "semibox");
                            break;
                    }
                }
            },
        },
        layoutWidth: {
            immediate: true,
            deep: true,
            handler(newVal, oldVal) {
                if (newVal !== oldVal) {
                    switch (newVal) {
                        case "fluid":
                            document.documentElement.setAttribute("data-layout-width", "fluid");
                            break;
                        case "boxed":
                            document.documentElement.setAttribute("data-layout-width", "boxed");
                            break;
                    }
                }
            },
        },
        position: {
            immediate: true,
            deep: true,
            handler(newVal, oldVal) {
                if (newVal !== oldVal) {
                    switch (newVal) {
                        case "fixed":
                            document.documentElement.setAttribute("data-layout-position", "fixed");
                            break;
                        case "scrollable":
                            document.documentElement.setAttribute("data-layout-position", "scrollable");
                            break;
                    }
                }
            },
        },
        topbar: {
            immediate: true,
            deep: true,
            handler(newVal, oldVal) {
                if (newVal !== oldVal) {
                    switch (newVal) {
                        case "light":
                            document.documentElement.setAttribute("data-topbar", "light");
                            break;
                        case "dark":
                            document.documentElement.setAttribute("data-topbar", "dark");
                            break;
                    }
                }
            },
        },
        sidebarSize: {
            immediate: true,
            deep: true,
            handler(newVal, oldVal) {
                if (newVal !== oldVal) {
                    switch (newVal) {
                        case "lg":
                            document.documentElement.setAttribute("data-sidebar-size", "lg");
                            break;
                        case "sm":
                            document.documentElement.setAttribute("data-sidebar-size", "sm");
                            break;
                        case "md":
                            document.documentElement.setAttribute("data-sidebar-size", "md");
                            break;
                        case "sm-hover":
                            document.documentElement.setAttribute("data-sidebar-size", "sm-hover");
                            break;
                    }
                }
            },
        },
        sidebarView: {
            immediate: true,
            deep: true,
            handler(newVal, oldVal) {
                if (newVal !== oldVal) {
                    switch (newVal) {
                        case "detached":
                            document.documentElement.setAttribute("data-layout-style", "detached");
                            break;
                        case "default":
                            document.documentElement.setAttribute("data-layout-style", "default");
                            break;
                    }
                }
            },
        },
        sidebarColor: {
            immediate: true,
            deep: true,
            handler(newVal, oldVal) {
                if (newVal !== oldVal) {
                    switch (newVal) {
                        case "dark":
                            document.documentElement.setAttribute("data-sidebar", "dark");
                            break;
                        case "light":
                            document.documentElement.setAttribute("data-sidebar", "light");
                            break;
                        case "gradient":
                            document.documentElement.setAttribute("data-sidebar", "gradient");
                            break;
                        case "gradient-2":
                            document.documentElement.setAttribute("data-sidebar", "gradient-2");
                            break;
                        case "gradient-3":
                            document.documentElement.setAttribute("data-sidebar", "gradient-3");
                            break;
                        case "gradient-4":
                            document.documentElement.setAttribute("data-sidebar", "gradient-4");
                            break;
                    }
                }
            },
        },
        sidebarImage: {
            immediate: true,
            deep: true,
            handler(newVal, oldVal) {
                if (newVal !== oldVal) {
                    switch (newVal) {
                        case "img-1":
                            document.documentElement.setAttribute("data-sidebar-image", "img-1");
                            break;
                        case "img-2":
                            document.documentElement.setAttribute("data-sidebar-image", "img-2");
                            break;
                        case "img-3":
                            document.documentElement.setAttribute("data-sidebar-image", "img-3");
                            break;
                        case "img-4":
                            document.documentElement.setAttribute("data-sidebar-image", "img-4");
                            break;
                        case "none":
                            document.documentElement.setAttribute("data-sidebar-image", "none");
                            break;
                    }
                }
            },
        },
        visibility: {
            immediate: true,
            deep: true,
            handler(newVal, oldVal) {
                if (newVal !== oldVal) {
                    switch (newVal) {
                        case "show":
                            document.documentElement.setAttribute("data-sidebar-visibility", "show");
                            break;
                        case "hidden":
                            document.documentElement.setAttribute("data-sidebar-visibility", "hidden");
                            break;
                    }
                }
            },
        },
    },
    components: {simplebar}
};


</script>

<template>
    <div>
        <div id="preloader">
            <div id="status">
                <div class="spinner-border text-primary avatar-sm" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>

        <BButton variant="danger" @click="topFunction" class="btn-icon" id="back-to-top">
            <i class="ri-arrow-up-line"></i>
        </BButton>
    </div>
</template>

<style lang="scss">
.b-overlay-wrap {
    .b-overlay {
        z-index: 1005 !important;
    }
}
</style>

