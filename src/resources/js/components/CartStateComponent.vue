<template>
    <a href="#" :class="baseClass" class="cart-state">
        <svg class="cart-state__ico">
            <use xlink:href="#cart-ico"></use>
        </svg>
        <span class="cart-state__title">
            Корзина
        </span>
    </a>
</template>

<script>
    export default {
        name: "CartStateComponent",

        props: {
            baseClass: {
                type: String,
                required: false,
                default: "nav-link"
            },

            cartTotal: {
                type: Number,
                required: false,
                default: 0
            },

            cartCount: {
                type: Number,
                required: false,
                default: 0
            },

            cartUrl: {
                type: String,
                required: true
            }
        },

        data() {
            return {
                total: 0,
                count: 0,
            }
        },

        created() {
            this.total = this.cartTotal;
            this.count = this.cartCount;
        },

        mounted() {
            this.$root.$on("change-cart", this.changeCartData);
        },

        methods: {
            changeCartData(cart) {
                this.total = cart.total;
                this.count = cart.count;
            }
        }
    }
</script>

<style scoped>

</style>