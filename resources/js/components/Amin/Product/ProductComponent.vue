<template>
  <div class="table-agile-info">
    <div class="panel panel-default">
      <div class="panel-heading"></div>
      <div class="row w3-res-tb">
        <div for="paginate" class="col-md-3 col-sm-2 col-4">
          <select
            v-model="paginate"
            class="form-control w-sm inline v-middle text-center"
          >
            <option
              v-for="item in limitNumber"
              :key="item.key"
              :value="item.key"
            >
              {{ item.value }}
            </option>
          </select>
        </div>
        <div class="col-md-4 col-sm-7 col-3" style="float: left"></div>
        <div class="col-md-5 col-sm-3 col-5" style="float: right">
          <input
            type="text"
            class="form-control"
            v-model="search"
            placeholder="Search"
          />
        </div>
      </div>

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
                Import product information
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
              <form
                @submit.prevent="addProduct"
                enctype="multipart/form-data"
                method="POST"
                ref="addForm"
                :action="formUrl"
              >
                <input type="hidden" name="productId" :value="product.id" />

                <input type="hidden" :value="csrfToken" name="_token" />

                <div class="form-group">
                  <label for="exampleInputEmail1">Name Product</label>
                  <input
                    type="text"
                    class="form-control"
                    id="exampleInputEmail1"
                    placeholder="Name"
                    name="name"
                    v-validate="'required'"
                    v-model="product.name"
                  />

                  <div style="color: red" role="alert">
                    {{ errors.first("name") }}
                  </div>
                  <div style="color: red" v-if="errorBackEnd.name">
                    {{ errorBackEnd.name[0] }}
                  </div>
                </div>
                <div class="form-group">
                  <div class="position-relative d-inline-block">
                    <label for="file_img_banner1">
                      <div class="img-drop-box mt-2 mr-2">
                        <img src ref="imageDispaly" class="img-thumbnail" />
                        <svg
                          width="45"
                          height="45"
                          viewBox="0 0 45 45"
                          style="
                            margin-top: 52px;
                            margin-left: 38%;
                            margin-right: 38%;
                          "
                          ref="iconFile"
                        >
                          <use
                            xlink:href="/images/Group_1287.svg#Group_1287"
                          ></use>
                        </svg>
                      </div>
                      <input
                        type="file"
                        id="file_img_banner1"
                        v-validate="'image_format'"
                        name="images"
                        ref="image"
                        v-on:change="attachImage"
                        accept="image/*"
                      />
                    </label>
                    <a
                      class="btn btn-light icon-close-white display-none"
                      style="background-color: black; border-radius: 91%"
                      ref="iconClose"
                      @click="deleteImage"
                    ></a>
                  </div>
                  <div style="color: red" role="alert">
                    {{ errors.first("images") }}
                  </div>
                  <div style="color: red" v-if="errorBackEnd.images">
                    {{ errorBackEnd.images[0] }}
                  </div>
                </div>

                <div class="form-group">
                  <label for="exampleInputEmail1"
                    >Import Price (.../1kg)
                  </label>
                  <input
                    type="text"
                    class="form-control"
                    name="priceImport"
                    v-validate="'required'"
                    v-model="product.import_price"
                  />
                  <div style="color: red" role="alert">
                    {{ errors.first("price") }}
                  </div>
                  <div style="color: red" v-if="errorBackEnd.price">
                    {{ errorBackEnd.price[0] }}
                  </div>
                </div>

                <div class="form-row">
                  <div
                    class="form-group col-md-3 text-center"
                    v-for="(data, index) in weight_product"
                    :key="data.id"
                  >
                    <label>{{ data.weight }}kg </label>
                    <div>
                      <input
                        type="text"
                        class="form-control text-center"
                        :name="'price[' + index + ']'"
                        v-validate="'required'"
                      />
                    </div>

                    <input
                      class="form-check-input"
                      type="hidden"
                      :value="data.weight"
                      :name="'weight[' + index + ']'"
                    />

                    <div style="color: red" role="alert">
                      {{ errors.first("price[" + index + "]") }}
                    </div>
                    <div style="color: red" v-if="errorBackEnd.price">
                      {{ errorBackEnd.price[0] }}
                    </div>
                  </div>
                </div>

                <div class="form-group">
                  <label for="exampleInputEmail1">Type_id</label>
                  <select
                    name="typeId"
                    v-model="product.type_id"
                    v-validate="'required'"
                    class="form-control"
                  >
                    <option
                      v-for="data in type_product"
                      :key="data.id"
                      :value="data.id"
                    >
                      {{ data.type }}
                    </option>
                  </select>
                  <div style="color: red" role="alert">
                    {{ errors.first("typeId") }}
                  </div>
                </div>

                <div class="form-group">
                  <label for="exampleInputEmail1">Description_id</label>
                  <select
                    name="descriptionId"
                    v-model="product.description_id"
                    v-validate="'required'"
                    class="form-control"
                  >
                    <option value="">Select product description</option>
                    <option
                      v-for="data in description_product"
                      :key="data.id"
                      :value="data.id"
                    >
                      {{ data.description }}
                    </option>
                  </select>
                  <div style="color: red" role="alert">
                    {{ errors.first("descriptionId") }}
                  </div>
                </div>

                <div class="form-group">
                  <label for="exampleInputEmail1">Content</label>
                  <quill-editor
                    v-model="product.content"
                    ref="myQuillEditor"
                    :options="editorOption"
                    @blur="onEditorBlur($event)"
                    @focus="onEditorFocus($event)"
                    @ready="onEditorReady($event)"
                  >
                  </quill-editor>

                  <!-- <ckeditor
                    name="content"
                    v-model="product.content"
                    :config="editorConfig"
                    :editor-url="editorUrl"
                  ></ckeditor> -->
                  <div style="color: red" role="alert">
                    {{ errors.first("content") }}
                  </div>
                  <div style="color: red" v-if="errorBackEnd.content">
                    {{ errorBackEnd.content[0] }}
                  </div>
                </div>

                <div class="modal-footer">
                  <button
                    type="button"
                    class="btn btn-secondary"
                    data-dismiss="modal"
                  >
                    Close
                  </button>
                  <button class="btn btn-primary">Save</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-striped b-t b-light text-center">
          <thead>
            <tr>
              <th scope="col">STT</th>
              <th scope="col">Name</th>
              <th scope="col">Images</th>
              <th scope="col" class="text-center">
                <a href="#" @click.prevent="change_sort('price')">Price</a>
                <span v-if="sort_direction == 'desc' && sort_field == 'price'"
                  >&uarr;</span
                >
                <span v-if="sort_direction == 'asc' && sort_field == 'price'"
                  >&darr;</span
                >
              </th>
              <th scope="col">Status</th>
              <th scope="col">TypeID</th>
              <th scope="col">DescriptionID</th>
              <th scope="col">Content</th>

              <th scope="col" class="text-center">
                <a href="#" @click.prevent="change_sort('quantity')"
                  >Quantity</a
                >
                <span
                  v-if="sort_direction == 'desc' && sort_field == 'quantity'"
                  >&uarr;</span
                >
                <span v-if="sort_direction == 'asc' && sort_field == 'quantity'"
                  >&darr;</span
                >
              </th>

              <th scope="col" class="text-center">
                <a href="#" @click.prevent="change_sort('import_price')"
                  >Import Price</a
                >
                <span
                  v-if="
                    sort_direction == 'desc' && sort_field == 'import_price'
                  "
                  >&uarr;</span
                >
                <span
                  v-if="sort_direction == 'asc' && sort_field == 'import_price'"
                  >&darr;</span
                >
              </th>

              <th scope="col" class="text-center">
                <a href="#" @click.prevent="change_sort('product_sold')"
                  >Sold</a
                >
                <span
                  v-if="
                    sort_direction == 'desc' && sort_field == 'product_sold'
                  "
                  >&uarr;</span
                >
                <span
                  v-if="sort_direction == 'asc' && sort_field == 'product_sold'"
                  >&darr;</span
                >
              </th>

              <th scope="col" class="text-center">
                <a href="#" @click.prevent="change_sort('views')">Views</a>
                <span v-if="sort_direction == 'desc' && sort_field == 'views'"
                  >&uarr;</span
                >
                <span v-if="sort_direction == 'asc' && sort_field == 'views'"
                  >&darr;</span
                >
              </th>
              <th></th>
            </tr>
          </thead>
          <transition-group name="slide-fade" tag="tbody">
            <tr v-for="(product, index) in products.data" :key="product.id">
              <th scope="row">{{ index + 1 }}</th>
              <td>
                {{ substring(product.name, 12) }}
              </td>
              <td>
                <img
                  v-lazy="baseUrl + '/uploads/products/' + product.images"
                  width="50px"
                  height="50px"
                  alt=""
                />
                <a
                  :href="`add-image/${product.id}`"
                  style="font-size: 21px; transform: translate(-26%, -14%)"
                  ><i
                    style="color: green"
                    class="fa fa-plus"
                    aria-hidden="true"
                  ></i
                ></a>

                <a :href="`detail-image/${product.id}`"
                  ><i
                    style="font-size: 21px; transform: translate(29%, 3%)"
                    class="fa fa-pencil-square-o text-success text-active"
                  ></i
                ></a>
              </td>
              <td>
                {{ product.price }}
              </td>
              <td v-if="product.price != 0">
                <span class="text-ellipsis">
                  <a
                    href="javascript:;"
                    v-if="product.status == 0"
                    @click="activeStatus(product)"
                    ><span class="fa-thumb-styling fa fa-thumbs-up"></span
                  ></a>
                  <a href="javascript:;" v-else @click="activeStatus(product)"
                    ><span class="fa-thumb-styling fa fa-thumbs-down"></span
                  ></a>
                </span>
              </td>
              <td v-else></td>
              <td>
                {{ product.type_id }}
              </td>
              <td>
                {{ product.description_id }}
              </td>
              <td>
                <p
                  style="
                    height: 70px;
                    position: relative;
                    -webkit-hyphens: auto;
                    hyphens: auto;
                    word-wrap: break-word;
                    overflow: auto;
                    width: 165px;
                  "
                  v-html="product.content"
                ></p>
              </td>
              <td>
                <a
                  style="font-size: 16px"
                  :href="`warehouse/${product.ware_houses_id}/edit`"
                >
                  {{ product.quantity }}
                </a>
              </td>
              <td>
                {{ product.import_price }}
              </td>
              <td>
                {{ product.product_sold }}
              </td>
              <td>
                {{ product.views }}
              </td>
              <td>
                <div class="td-action">
                  <a
                    data-toggle="modal"
                    data-target="#myModal"
                    @click="
                      updateProduct(product),
                        ((buttonAdd = false), (edit = true))
                    "
                    style="font-size: 21px; transform: translate(-26%, -14%)"
                    ><i
                      class="fa fa-pencil-square-o text-success text-active"
                    ></i
                  ></a>
                </div>
              </td>
            </tr>
          </transition-group>
        </table>
      </div>
    </div>
    <div v-if="products != ''">
      <nav aria-label="Page navigation example">
        <paginate
          v-model="page"
          :page-count="parseInt(products.last_page)"
          :page-range="5"
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
      <FlashMessage :position="'left bottom'"></FlashMessage>
    </div>
    <div class="text-center" v-else style="color: red">There is no data !</div>
    <loader :flag-show="flagShowLoader"></loader>
  </div>
</template>

<style lang="scss" scoped>
.td-action {
  display: inline-flex;
}
.btn-success {
  float: right;
  margin-top: 10px;
  margin-right: 5%;
}
</style>

<script>
import Loader from "../../Common/loader.vue";
const axios = require("axios").default;
export default {
  data() {
    return {
      baseUrl: Laravel.baseUrl, //Gọi thay cho đg dẫn http://127.0.0.1:8000
      csrfToken: Laravel.csrfToken,
      buttonAdd: true,

      update_comment: [],
      selectedIds: [],

      productImages: [],
      productImage: {
        id: "",
        product_id: "",
        url: "",
      },
      listImages: [],

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
        quantity: "",
        import_price: "",
        product_sold: "",
        ware_houses_id: "",
        views: "",
      },

      errorBackEnd: {},
      edit: false,
      page: 1,
      paginate: 5,
      search: "",
      type_product: {},
      description_product: {},
      weight_product: {},
      flagShowLoader: false,
      limitNumber: [],
      editorOption: {},
      sort_direction: "desc",
      sort_field: "created_at",

      editorUrl: "https://cdn.ckeditor.com/4.14.1/full-all/ckeditor.js",
      editorConfig: {
        toolbarGroups: [
          { name: "document", groups: ["mode", "document", "doctools"] },
          { name: "clipboard", groups: ["clipboard", "undo"] },
          {
            name: "editing",
            groups: ["find", "selection", "spellchecker", "editing"],
          },
          { name: "forms", groups: ["forms"] },
          { name: "basicstyles", groups: ["basicstyles", "cleanup"] },
          {
            name: "paragraph",
            groups: ["list", "indent", "blocks", "align", "bidi", "paragraph"],
          },
          { name: "links", groups: ["links"] },
          { name: "insert", groups: ["insert"] },
          { name: "styles", groups: ["styles"] },
          { name: "colors", groups: ["colors"] },
          { name: "tools", groups: ["tools"] },
          { name: "others", groups: ["others"] },
          { name: "about", groups: ["about"] },
        ],
        removeButtons:
          "NewPage,Print,Save,Templates,Replace,Find,SelectAll,Scayt,Form,Checkbox,Radio,TextField,Textarea,Select,Button,ImageButton,HiddenField,CreateDiv,Anchor,Flash,Smiley,PageBreak,ShowBlocks,About,Language,Iframe,Image",
      },
    };
  },
  created() {
    let messError = {
      custom: {
        name: {
          required: "* Tên chưa nhập",
        },
        images: {
          required: "* Ảnh chưa chọn",
          image_format: "* Ảnh chưa đúng định dạng",
        },
        price: {
          required: "* Giá chưa nhập",
        },
        type_id: {
          required: "* Thể loại chưa nhập",
        },
        description_id: {
          required: "* Mô tả chưa nhập",
        },
        content: {
          required: "* Nội dung chưa nhập",
        },
        status: {
          required: "* Trạng thái chưa nhập",
        },
      },
    };
    this.$validator.localize("en", messError);
    this.fetchData();
    this.fill_Product();
    this.getLimitNumber();
  },
  props: ["formUrl"],
  mounted() {},
  components: {
    Loader,
  },
  watch: {
    paginate: function (value) {
      this.fetchData();
    },
    search: function (value) {
      this.fetchData();
    },
  },
  computed: {
    editor() {
      return this.$refs.myQuillEditor.quill;
    },
  },
  methods: {
    addProduct: function (e) {
      e.preventDefault();
      let that = this;
      let formData = new FormData(this.$refs.addForm);
      formData.append("content", this.product.content);
      this.$validator.validateAll().then((valid) => {
        if (valid) {
          axios
            .post(`/admin/product/update`, formData, {
              header: {
                "Content-Type": "multipart/form-data",
              },
            })
            .then((response) => {
              this.$swal({
                title: "Update Successfully!",
                icon: "success",
                confirmButtonText: "OK",
              }).then(function (confirm) {
                window.location.href = "/admin/product";
              });
            });
        }
      });
    },
    // AddProduct(e) {
    // let formData = new FormData();
    // formData.append("id", this.product.id);
    // formData.append("name", this.product.name);
    // formData.append("images", this.product.images);
    // formData.append("price", this.product.price);
    // formData.append("type_id", this.product.type_id);
    // formData.append("description_id", this.product.description_id);
    // formData.append("content", this.product.content);
    // formData.append("priceImport",priceImport);
    // axios
    //   .post(`product/update`, this.update_comment, {
    //     headers: {
    //       "Content-Type": "multipart/form-data",
    //     },
    //   })
    //   .then((response) => {
    //     that.fetchData();
    //     that
    //       .$swal({
    //         title: "Update success!",
    //         icon: "success",
    //         confirmButtonText: "Yes !",
    //         confirmButtonColor: "#3085d6",
    //       })
    //       .then(function (confirm) {
    //         if (confirm.isConfirmed) {
    //           that.buttonAdd = true;
    //           (window.location = response.data),
    //             (that.product = {
    //               name: "",
    //               images: "",
    //               price: "",
    //               type_id: "",

    //               description_id: "",
    //               content: "",
    //               status: "",
    //             });
    //         } else {
    //         }
    //       });
    //   })
    //   .catch((err) => {
    //   switch (err.response.status) {
    //     case 422:
    //       that.errorBackEnd = err.response.data.errors;
    //       break;
    //     case 500:
    //       that
    //         .$swal({
    //           title: "Update Failure Data!",
    //           icon: "error",
    //           confirmButtonText: "Ok",
    //         })
    //         .then(function (confirm) {});
    //       break;
    //     default:
    //       break;
    //   }
    // });
    // },

    // updateProductImage(product) {
    //   this.productImage.product_id = product.id;
    // },
    // attachImageAdd(type, value) {
    //   var file = null;
    //   if (type == "image") {
    //     if (value < this.listImages.length) {
    //       this.listImages[value].file = this.$refs[type + value][0].files[0];
    //       file = this.$refs[type + value][0].files[0];
    //       this.$refs[type + value][0].value = "";
    //     } else {
    //       this.listImages.push({
    //         file_id: null,
    //         file: this.$refs[type + value].files[0],
    //         src: "",
    //       });
    //       file = this.$refs[type + value].files[0];
    //       this.$refs[type + value].value = "";
    //     }
    //   }
    //   let reader = new FileReader();
    //   reader.addEventListener(
    //     "load",
    //     function () {
    //       this.$refs[type + "Display" + value][0].style.setProperty(
    //         "display",
    //         "block",
    //         "important"
    //       );
    //       this.$refs[type + "IconClose" + value][0].style.setProperty(
    //         "display",
    //         "block",
    //         "important"
    //       );
    //       this.$refs[type + "Display" + value][0].src = reader.result;
    //       if (type == "image") {
    //         this.listImages[value].src = reader.result;
    //       }
    //       this.$refs[type + "IconPlus" + value][0].style.setProperty(
    //         "display",
    //         "none",
    //         "important"
    //       );
    //     }.bind(this),
    //     false
    //   );
    //   reader.readAsDataURL(file);
    // },

    // deleteImageAdd(type, value) {
    //   var index = 0;
    //   if (type == "image") {
    //     index = this.listImages.length;
    //     this.listImages.splice(value, 1);
    //   }
    //   this.$refs[type + index].value = "";
    // },

    // AddProductImage() {
    //   let that = this;
    //   let formData = new FormData();
    //   this.listImages.forEach((item) => {
    //     formData.append("images[]", item.file);
    //   });

    //   axios
    //     .post(
    //       `product-image/${that.productImage.product_id}/update`,
    //       formData,
    //       {
    //         headers: {
    //           "Content-Type": "multipart/form-data",
    //         },
    //       }
    //     )
    //     .then((response) => {
    //       that
    //         .$swal({
    //           title: "Update success!",
    //           icon: "success",
    //           confirmButtonText: "Yes !",
    //           confirmButtonColor: "#3085d6",
    //         })
    //         .then(function (confirm) {
    //           window.location.href = "/admin/product";
    //         });
    //     })
    //     .catch((err) => {
    //       switch (err.response.status) {
    //         case 422:
    //           that.errorBackEnd = err.response.data.errors;
    //           break;
    //         case 500:
    //           that
    //             .$swal({
    //               title: "Update Failure Data!",
    //               icon: "error",
    //               confirmButtonText: "Ok",
    //             })
    //             .then(function (confirm) {});
    //           break;
    //         default:
    //           break;
    //       }
    //     });
    // },
    change_sort(field) {
      if (this.sort_field == field) {
        this.sort_direction = this.sort_direction == "asc" ? "desc" : "asc";
      } else {
        this.sort_field = field;
      }
      this.fetchData(1);
    },
    activeStatus(product) {
      let that = this;
      let formData = new FormData();
      formData.append("status", product.status);
      // formData.append("_method", "put");
      axios
        .post(`update-product-status/${product.id}`, formData, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        })
        .then((res) => {
          this.$swal({
            title: "Update Status successfully!",
            icon: "success",
            confirmButtonText: "OK",
          }).then(function (confirm) {
            that.fetchData();
          });
        })
        .catch((err) => {
          that.flashMessage.error({
            message: "Update Status Failure!",
            icon: "/backend/icon/error.svg",
            blockClass: "text-centet",
          });
        });
    },
    fill_Product() {
      axios
        .get(`fill-product`, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        })
        .then((res) => {
          this.type_product = res.data.type_product;
          this.description_product = res.data.description_product;
          this.weight_product = res.data.weight_product;
        });
    },
    // attachFile() {
    //   this.product.images = this.$refs.fileImage.files[0];
    //   let reader = new FileReader();
    //   reader.buttonAddEventListener(
    //     "load",
    //     function () {
    //       this.$refs.fileImageDispaly.src = reader.result;
    //     }.bind(this),
    //     false
    //   );

    //   reader.readAsDataURL(this.product.images);
    // },

    // AddProduct(e) {
    // let that = this;
    // if (this.edit == false) {
    //   let formData = new FormData();
    //   formData.append("name", this.product.name);
    //   formData.append("images", this.product.images);
    //   formData.append("price", this.product.price);
    //   formData.append("type_id", this.product.type_id);
    //   formData.append("description_id", this.product.description_id);
    //   formData.append("content", this.product.content);
    //   formData.append("status", this.product.status);
    //   axios
    //     .post(`product-add`, formData, {
    //       headers: {
    //         "Content-Type": "multipart/form-data",
    //       },
    //     })
    //     .then((response) => {
    //       that.fetchData();
    //       that.flashMessage.setStrategy("multiple");
    //       that.flashMessage.success({
    //         message: "Thêm Thành Công !",
    //         icon: "/backend/icon/check.svg",
    //         blockClass: "text-centet",
    //       });
    //       that
    //         .$swal({
    //           title: "buttonAdd success!",
    //           icon: "success",
    //           showCancelButton: true,
    //           confirmButtonText: "Yes !",
    //           confirmButtonColor: "#3085d6",
    //           cancelButtonText: "Cancle !",
    //           cancelButtonColor: "#d33",
    //         })
    //         .then(function (confirm) {
    //           if (confirm.isConfirmed) {
    //             that.product = {
    //               name: "",
    //               images: "",
    //               price: "",
    //               type_id: "",
    //               description_id: "",
    //               content: "",
    //               status: "",
    //             };
    //           } else {
    //             window.location = response.data;
    //           }
    //         });
    //     })
    //     .catch((err) => {
    //       switch (err.response.status) {
    //         case 422:
    //           that.errorBackEnd = err.response.data.errors;
    //           break;
    //         case 500:
    //           that
    //             .$swal({
    //               title: "buttonAdd Failure Data!",
    //               icon: "error",
    //               confirmButtonText: "Ok",
    //             })
    //             .then(function (confirm) {});
    //           break;
    //         default:
    //           break;
    //       }
    //     });
    // } else {
    //   let formData = new FormData();
    //   formData.append("id", this.product.id);
    //   formData.append("name", this.product.name);
    //   formData.append("images", this.product.images);
    //   formData.append("price", this.product.price);
    //   formData.append("type_id", this.product.type_id);
    //   formData.append("description_id", this.product.description_id);
    //   formData.append("content", this.product.content);
    //   axios
    //     .post(`product/update`, formData, {
    //       headers: {
    //         "Content-Type": "multipart/form-data",
    //       },
    //     })
    //     .then((response) => {
    //       that.fetchData();
    //       that
    //         .$swal({
    //           title: "Update success!",
    //           icon: "success",
    //           confirmButtonText: "Yes !",
    //           confirmButtonColor: "#3085d6",
    //         })
    //         .then(function (confirm) {
    //           if (confirm.isConfirmed) {
    //             that.buttonAdd = true;
    //             (window.location = response.data),
    //               (that.product = {
    //                 name: "",
    //                 images: "",
    //                 price: "",
    //                 type_id: "",

    //                 description_id: "",
    //                 content: "",
    //                 status: "",
    //               });
    //           } else {
    //           }
    //         });
    //     })
    //     .catch((err) => {
    //       switch (err.response.status) {
    //         case 422:
    //           that.errorBackEnd = err.response.data.errors;
    //           break;
    //         case 500:
    //           that
    //             .$swal({
    //               title: "Update Failure Data!",
    //               icon: "error",
    //               confirmButtonText: "Ok",
    //             })
    //             .then(function (confirm) {});
    //           break;
    //         default:
    //           break;
    //       }
    //     });
    // }
    // },
    fetchData() {
      let that = this;
      this.flagShowLoader = true;
      axios
        .get(
          "get-product?page=" +
            that.page +
            "&paginate=" +
            that.paginate +
            "&search=" +
            that.search +
            "&sort_direction=" +
            that.sort_direction +
            "&sort_field=" +
            that.sort_field
        )
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
    updateProduct(product) {
      this.product.id = product.id;
      this.product.name = product.name;
      if (product.images != "") {
        this.$refs.imageDispaly.src =
          this.baseUrl + "/uploads/products/" + product.images;
        this.$refs.imageDispaly.style.display = "block";
        this.$refs.iconClose.style.display = "block";
        this.$refs.iconFile.style.display = "none";
      }

      this.product.price = product.price;
      this.product.type_id = product.type_id;
      this.product.description_id = product.description_id;
      this.product.content = product.content;
      this.product.import_price = product.import_price;
    },
    deleteProduct(id) {
      let that = this;
      this.$swal({
        title: "Do you want to delete ？",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "No, cancel!",
      }).then((result) => {
        if (result.value) {
          axios
            .get(`product/${id}/delete`)
            .then((response) => {
              this.$swal({
                title: "Delete successfully!",
                icon: "success",
                confirmButtonText: "OK",
              }).then(function (confirm) {});
              that.fetchData();
            })
            .catch((error) => {
              that.flashMessage.error({
                message: "Delete Failure!",
                icon: "/backend/icon/error.svg",
                blockClass: "text-centet",
              });
            });
        }
      });
    },
    attachImage() {
      this.product.images = this.$refs.image.files[0];
      let reader = new FileReader();
      reader.addEventListener(
        "load",
        function () {
          this.$refs.imageDispaly.style.display = "block";
          this.$refs.iconClose.style.display = "block";
          this.$refs.imageDispaly.src = reader.result;
          this.$refs.iconFile.style.display = "none";
        }.bind(this),
        false
      );
      reader.readAsDataURL(this.product.images);
    },
    deleteImage() {
      this.product.images = "";
      this.$refs.imageDispaly.style.display = "none";
      this.$refs.iconClose.style.display = "none";
      this.$refs.image.value = "";
      this.$refs.iconFile.style.display = "block";
    },
    substring(str, value) {
      if (str.length <= value) {
        return str;
      } else {
        return str.slice(0, value) + "…";
      }
    },
    getLimitNumber() {
      axios.get(`/get-limit-number`).then((response) => {
        this.limitNumber = response.data;
      });
    },
    onEditorBlur(quill) {
      console.log("editor blur!", quill);
    },
    onEditorFocus(quill) {
      console.log("editor focus!", quill);
    },
    onEditorReady(quill) {
      console.log("editor ready!", quill);
    },
    onEditorChange({ quill, html, text }) {
      console.log("editor change!", quill, html, text);
      this.content = html;
    },
  },
};
</script>
