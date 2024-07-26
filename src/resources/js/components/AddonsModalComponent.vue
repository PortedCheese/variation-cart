<template v-if="Object.keys(addonVariations).length">

</template>

<script>
    import productVariationEventBus from '../category-product/categoryProductEventBus';

    export default {
        name: "AddonsModalComponent",

        props: {

        },

        data() {
            return {
                loading: false,
                addonVariations: [],
                messages: [],
                errors: [],
            }
        },
        mounted() {
            productVariationEventBus.$on("change-order-addons", this.changeOrderAddonsData);
            productVariationEventBus.$on("remove-order-addons", this.removeOrderAddonsData);
        },

        methods: {
            resetData() {
                this.messages = [];
                this.errors = [];
                this.quantity = 1;
            },
            changeOrderAddonsData(data){
                this.addonVariations.push(data);
                this.addonVariationsSumm = this.addonsSumm()
            },
            removeOrderAddonsData(data){
                this.addonVariations.splice(this.addonVariations.indexOf(data),1);
                this.addonVariationsSumm = this.addonsSumm()
            }
        }
    }
</script>

<style scoped>

</style>