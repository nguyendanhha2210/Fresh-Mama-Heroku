<template>
  <div class="success-email">
    <div class="row justify-content-center">
              <h2 class="text-center h2-successmail">Email sent successfully</h2>
            <div class="col-md-9 col-lg-12 text-center" style="text-align: center;"></div>
              <img src="image/changePasswordComplete.png" class="w-24" />
            </div>
            <div class="col-md-9 col-lg-12 text-center" style="text-align: center;">
                <a :href="formLogin" class="btn btn-link px-0 mt-3">Back to Login</a>
      </div>
    </div>
  </div>
</template>
<style scoped>
.h2-successmail {
  color: white;
}
.btn-link {
  font-size: 19px;
}
</style>

<script>
export default {
  props: ["formLogin"],
  created: function () {},
  data() {
    return {};
  },
  mounted() {},
  methods: {},
};
</script>