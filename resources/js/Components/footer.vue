<script>
export default {
    data() {
        return {
            appn: import.meta.env.VITE_APP_NAME || 'Laravel',
        };
    },
};
</script>

<template>
    <footer class="footer">
        <BContainer fluid>
            <BRow>
                <BCol col sm="6">
                    {{ new Date().getFullYear() }} © <span v-text="appn"></span>. All Rights Reserved.
                </BCol>
                <BCol col sm="6">
                    <div class="text-sm-end d-none d-sm-block">
                        Crafted with <i class="mdi mdi-heart text-danger"></i>
                    </div>
                </BCol>
            </BRow>
        </BContainer>
    </footer>
</template>
