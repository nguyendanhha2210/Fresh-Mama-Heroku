<template>
  <div class="pt-3" style="min-height: 100%; position: relative">
    <div
      class="product-show-option"
      style="background-color: #e9edf0; height: 54px"
      v-if="products != ''"
    >
      <div class="row">
        <div
          class="col-lg-2 col-md-2 col-3"
          style="transform: translate(10%, 23%)"
        >
          <select v-model="paginate" class="form-control text-center">
            <option
              v-for="item in limitNumber"
              :key="item.key"
              :value="item.key"
            >
              {{ item.value }}
            </option>
          </select>
        </div>

        <div
          class="col-lg-3 col-md-3 col-2"
          style="transform: translate(10%, 23%)"
        >
          <select
            v-model="statusView"
            class="form-control w-sm inline v-middle text-center"
          >
            <option
              v-for="item in sortOption"
              :key="item.key"
              :value="item.key"
            >
              {{ item.value }}
            </option>
          </select>
        </div>

        <div class="col-lg-3 col-md-3 col-2"></div>

        <div
          class="col-lg-4 col-md-4 col-5 text-right"
          style="transform: translate(-4%, 23%)"
        >
          <input
            type="text"
            class="form-control"
            v-model="search"
            placeholder="Search"
          />
        </div>
      </div>
    </div>

    <div class="product-list" style="background-color: #e9edf0">
      <div class="row" style="padding-top: 25px">
        <div
          class="col-lg-4 col-sm-6"
          v-for="product in products.data"
          :key="product.id"
        >
          <div class="product-item" style="background-color: white">
            <div class="pi-pic">
              <img
                style="height: 250px"
                v-lazy="baseUrl + '/uploads/products/' + product.images"
                alt=""
              />
              <div class="sale pp-sale">Sale</div>
              <div class="icon">
                <i class="icon_heart_alt"></i>
              </div>
              <ul>
                <li class="w-icon active">
                  <a @click="addCartProduct(product)" href="#"
                    ><i class="fa fa-shopping-basket"></i
                  ></a>
                </li>
                <li class="quick-view">
                  <a
                    type="button"
                    data-toggle="modal"
                    data-target="#myModal"
                    @click="showQuickView(product)"
                    href="#"
                    >+ Quick View</a
                  >
                </li>

                <li class="w-icon">
                  <a :href="`/product-detail/${product.id}`"
                    ><i class="fa fa-eye"></i
                  ></a>
                </li>
              </ul>
            </div>
            <div
              class="pi-text"
              style="padding-top: 19px !important; border: 0.5px solid #e9edf0"
            >
              <a
                href="#"
                style="transform: translate(0%, -34%); font-size: 21px"
              >
                <h5 style="">
                  {{ product.name }}
                </h5>
              </a>
              <div style="color: red; transform: translate(-27%, 53%)">
                <u
                  style="
                    font-size: 13px;
                    display: -webkit-inline-box;
                    transform: translate(0%, -13%);
                  "
                  >đ</u
                >
                <span style="font-size: 19px">{{
                  formatPrice(product.price)
                }}</span>
              </div>
              <div
                class="da-ban"
                style="
                  transform: translate(32%, -47%);
                  font-size: 14px;
                  color: dimgray;
                "
              >
                <span>Đã bán {{ product.product_sold }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div
      class="loading-more"
      v-if="products != ''"
      style="position: absolute; bottom: 1px; left: 50%; right: 50%"
    >
      <nav aria-label="Page navigation example" style="height: 44px">
        <paginate
          v-model="page"
          :page-count="parseInt(products.last_page)"
          :page-range="9"
          :margin-pages="2"
          :click-handler="changePage"
          :prev-text="'<<'"
          :next-text="'>>'"
          :container-class="'pagination justify-content-center'"
          :page-class="'page-item'"
          :prev-class="'page-item'"
          :next-class="'page-item'"
          :page-link-class="'page-link bg-info text-light'"
          :next-link-class="'page-link bg-info text-light'"
          :prev-link-class="'page-link bg-info text-light'"
        >
        </paginate>
      </nav>
    </div>
    <div class="text-center" v-else style="color: red">There is no data !</div>

    <div
      class="modal fade"
      id="myModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="exampleModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">
              Product information
            </h5>
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="form-group row">
              <label for="staticEmail" class="col-lg-4 col-sm-4 col-form-label"
                >Name Product :
              </label>
              <div class="col-lg-8 col-sm-8">
                <input
                  type="text"
                  readonly
                  class="form-control-plaintext"
                  id="staticEmail"
                  v-model="product.name"
                />
              </div>
            </div>

            <div class="form-group row">
              <label for="staticEmail" class="col-lg-4 col-sm-4 col-form-label"
                >Price Product :
              </label>
              <div class="col-lg-8 col-sm-8">
                <input
                  type="text"
                  readonly
                  class="form-control-plaintext"
                  id="staticEmail"
                  v-model="product.price"
                />
              </div>
            </div>

            <div class="form-group row">
              <label for="staticEmail" class="col-lg-4 col-sm-4 col-form-label"
                >Content Product :
              </label>
              <div class="col-lg-8 col-sm-8">
                <input
                  type="text"
                  readonly
                  class="form-control-plaintext"
                  id="staticEmail"
                  v-model="product.content"
                />
              </div>
            </div>

            <div class="form-group row">
              <label for="staticEmail" class="col-lg-4 col-sm-4 col-form-label"
                >Images Product :
              </label>
              <div class="col-lg-8 col-sm-8">
                <img src ref="fileImageDispaly" width="150px" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import Loader from "../../../Common/loader.vue";
import Vue from "vue";
import axios from "axios";
export default {
  data() {
    return {
      baseUrl: Laravel.baseUrl, //Gọi thay cho đg dẫn http://127.0.0.1:8000
      descr: this.productDescription,

      products: [],
      product: {
        id: "",
        name: "",
        images: "",
        price: "",
        type_id: "",
        description_id: "",
        content: "",
        status: "",
      },
      page: 1,
      paginate: 9,
      search: "",
      statusView: 0,
      flagShowLoader: false,
      sortOption: [],
      limitNumber: [],
    };
  },
  created() {
    this.fetchData();
    this.getSortOption();
    this.getLimitNumber();
  },
  components: {
    Loader,
  },
  props: ["productDescription"],
  mounted() {},

  watch: {
    paginate: function (value) {
      this.fetchData();
    },
    search: function (value) {
      this.fetchData();
    },
    statusView: function (value) {
      this.fetchData();
    },
  },
  methods: {
    formatPrice(value) {
      let val = (value / 1).toFixed(0).replace(".", ",");
      return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    },
    fetchData() {
      let that = this;
      this.flagShowLoader = true;
      var url =
        "/get-description-product/" +
        this.descr[0].description_id +
        "?page=" +
        this.page +
        "&paginate=" +
        this.paginate +
        "&search=" +
        this.search +
        "&statusView=" +
        that.statusView;
      axios
        .get(url)
        .then(function (response) {
          that.products = response.data;
          that.flagShowLoader = false;
        })
        .catch((err) => {
          switch (err.response.status) {
            case 404:
              that
                .$swal({
                  title: "Error loading data !",
                  icon: "warning",
                  confirmButtonText: "Ok",
                })
                .then(function (confirm) {});
              break;
            default:
              break;
          }
        });
    },

    prev() {
      if (this.products.prev_page_url) {
        this.page--;
        this.fetchData();
      }
    },
    next() {
      if (this.products.next_page_url) {
        this.page++;
        this.fetchData();
      }
    },

    changePage(page) {
      this.page = page;
      this.fetchData();
    },

    showQuickView(product) {
      this.product.id = product.id;
      this.product.name = product.name;
      if (product.images != "") {
        this.$refs.fileImageDispaly.src =
          this.baseUrl + "/uploads/products/" + product.images;
      }

      this.product.price = product.price;
      this.product.type_id = product.type_id;
      this.product.weight_id = product.weight_id;
      this.product.description_id = product.description_id;
      this.product.content = product.content;
      this.product.status = product.status;
    },

    attachFile() {
      this.product.images = this.$refs.fileImage.files[0];
      let reader = new FileReader();
      reader.buttonAddEventListener(
        "load",
        function () {
          this.$refs.fileImageDispaly.src = reader.result;
        }.bind(this),
        false
      );

      reader.readAsDataURL(this.product.images);
    },
    getSortOption() {
      axios.get(`/get-sort-option`).then((response) => {
        this.sortOption = response.data;
      });
    },
    getLimitNumber() {
      axios.get(`/get-limit-number`).then((response) => {
        this.limitNumber = response.data;
      });
    },
  },
};
</script>