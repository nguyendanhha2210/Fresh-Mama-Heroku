<template>
  <div style="background-color: #e9edf0; min-height: 100%; position: relative">
    <section class="pt-5 pb-5">
      <div class="container" style="background-color: white">
        <div class="row">
          <div
            class="col-lg-3 col-md-6 col-sm-8 order-2 order-lg-1"
            style="border-right: 1px solid #e9edf0"
          >
            <div class="blog-sidebar" style="margin-top: 12px">
              <div class="search-form">
                <form action="#">
                  <input
                    type="text"
                    placeholder="Search"
                    class="form-control"
                    v-model="search"
                  />
                </form>

                <select
                  style="height: 46px; margin-top: 27px"
                  v-model="id_category"
                  class="form-control w-sm inline v-middle"
                >
                  <option value="">Filter By Category</option>
                  <option
                    v-for="post in categoryPosts"
                    :key="post.id"
                    :value="post.id"
                  >
                    {{ post.name }}
                  </option>
                </select>
              </div>

              <div class="recent-post">
                <h4>Recent Post</h4>
                <div class="recent-blog">
                  <a
                    href="#"
                    class="rb-item"
                    v-for="post in recentPosts"
                    :key="post.id"
                  >
                    <div class="rb-pic">
                      <a :href="`blog/${post.id}/detail`"
                        ><img
                          style="height: 70px"
                          :src="baseUrl + '/uploads/posts/' + post.images"
                          alt=""
                      /></a>
                    </div>
                    <div class="rb-text">
                      <a :href="`blog/${post.id}/detail`"
                        ><h6
                          style="
                            width: 165px;
                            white-space: pre-wrap;
                            overflow: hidden;
                            text-overflow: ellipsis;
                            -webkit-line-clamp: 1;
                            -webkit-box-orient: vertical;
                            display: -webkit-box;
                          "
                        >
                          {{ post.title }}
                        </h6></a
                      >
                      <p>
                        {{ post.categorypost.name
                        }}<span>- {{ post.created_at | formatDate }}</span>
                      </p>
                    </div>
                  </a>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-9 order-1 order-lg-2">
            <div class="row">
              <div
                class="col-lg-6 col-sm-6"
                v-for="post in posts.data"
                :key="post.id"
              >
                <div class="blog-item" style="background-color: #e9edf0">
                  <div class="bi-pic" style="margin-top: 14px">
                    <img
                      style="height: 300px"
                      :src="baseUrl + '/uploads/posts/' + post.images"
                      alt=""
                    />
                  </div>
                  <div
                    class="bi-text"
                    style="padding-left: 15px; padding-bottom: 15px"
                  >
                    <a :href="`blog/${post.id}/detail`"
                      ><h4
                        style="
                          width: 391px;
                          white-space: pre-wrap;
                          overflow: hidden;
                          text-overflow: ellipsis;
                          -webkit-line-clamp: 2;
                          -webkit-box-orient: vertical;
                          display: -webkit-box;
                        "
                      >
                        {{ post.title }}
                      </h4></a
                    >
                    <p>
                      {{ post.categorypost.name
                      }}<span>- {{ post.created_at | formatDate }}</span>
                    </p>
                  </div>
                </div>
              </div>

              <div class="col-lg-12">
                <div class="loading-more" v-if="posts != ''">
                  <nav aria-label="Page navigation example">
                    <paginate
                      v-model="page"
                      :page-count="parseInt(posts.last_page)"
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
                </div>
                <div class="text-center" v-else style="color: red">
                  There is no data !
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- Blog Section End -->

    <Modal
      v-if="modalShow"
      :type="type"
      :title="title"
      :text="text"
      :confirm="confirm"
      :cancle="cancle"
      :urlConfirm="urlConfirm"
      :urlCancle="urlCancle"
      :modalShow="modalShow"
    ></Modal>
    <Loader :flag-show="flagShowLoader"></Loader>
  </div>
</template>
<script>
import Modal from "../../Modal/Modal.vue";
import Loader from "../../Common/loader.vue";
import Vue from "vue";
import axios from "axios";
export default {
  data() {
    return {
      baseUrl: Laravel.baseUrl, //Gọi thay cho đg dẫn http://127.0.0.1:8000
      posts: [],
      recentPosts: [],
      categoryPosts: [],
      post: {
        title: "",
        id_category_post: "",
        desc: "",
        images: "",
        status: "",
        content: "",
      },
      page: 1,
      paginate: 4,
      search: "",
      id_category: "",
      flagShowLoader: false,
      //Modal
      modalShow: false,
      type: "",
      title: "",
      text: "",
      confirm: "",
      cancle: "",
      urlConfirm: "",
      urlCancle: "",
      //Modal
    };
  },
  created() {
    this.fetchData();
  },
  watch: {
    paginate: function (value) {
      this.fetchData();
    },
    search: function (value) {
      this.fetchData();
    },
    id_category: function (value) {
      this.fetchData();
    },
  },
  components: {
    Modal,
    Loader,
  },
  mounted() {},

  methods: {
    fetchData() {
      let that = this;
      this.flagShowLoader = true;
      axios
        .get(
          "get-post-list?page=" +
            that.page +
            "&paginate=" +
            that.paginate +
            "&search=" +
            that.search +
            "&id_category=" +
            that.id_category
        )
        .then(function (response) {
          that.posts = response.data.post; //show data ra
          that.recentPosts = response.data.recentPost; //show data ra
          that.categoryPosts = response.data.categoryPost; //show data ra
          that.flagShowLoader = false;
        })
        .catch((err) => {
          switch (err.response.status) {
            case 500:
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
      if (this.posts.prev_page_url) {
        this.page--;
        this.fetchData();
      }
    },
    next() {
      if (this.posts.next_page_url) {
        this.page++;
        this.fetchData();
      }
    },

    changePage(page) {
      this.page = page;
      this.fetchData();
    },
  },
};
</script>