<template>
    <section>
        <div class="alert alert-danger alert-dismissible fade show" role="alert" v-if="error && error_message != ''">
            {{ error_message }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="alert alert-success alert-dismissible fade show" role="alert"
             v-if="success && success_message != ''">
            {{ success_message }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="">Product Name</label>
                            <input type="text" v-model="product_name" placeholder="Product Name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="">Product SKU</label>
                            <input type="text" v-model="product_sku" placeholder="Product Name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="">Description</label>
                            <textarea v-model="description" id="" cols="30" rows="4" class="form-control"></textarea>
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Media</h6>
                    </div>
                    <div class="card-body border">
                        <vue-dropzone ref="myVueDropzone"
                                      id="dropzone"
                                      :options="dropzoneOptions"
                                      @vdropzone-complete="imageUploadHandler">
                        </vue-dropzone>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Variants</h6>
                    </div>
                    <div class="card-body">
                        <div class="row" v-for="(item,index) in product_variant">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">Option</label>
                                    <select v-model="item.option" class="form-control">
                                        <option v-for="variant in variants"
                                                :value="variant.id">
                                            {{ variant.title }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label v-if="product_variant.length != 1"
                                           @click="product_variant.splice(index,1); checkVariant"
                                           class="float-right text-primary"
                                           style="cursor: pointer;">Remove</label>
                                    <label v-else for="">.</label>
                                    <input-tag v-model="item.tags" @input="checkVariant"
                                               class="form-control"></input-tag>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer"
                         v-if="product_variant.length < variants.length && product_variant.length < 3">
                        <button @click="newVariant" class="btn btn-primary">Add another option</button>
                    </div>

                    <div class="card-header text-uppercase">Preview</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <td>Variant</td>
                                    <td>Price</td>
                                    <td>Stock</td>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="variant_price in product_variant_prices">
                                    <td>{{ variant_price.title }}</td>
                                    <td>
                                        <input type="text" class="form-control" v-model="variant_price.price">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" v-model="variant_price.stock">
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button @click="updateProduct" type="submit" class="btn btn-lg btn-primary" v-if="edit_mode">Update</button>
        <button @click="saveProduct" type="submit" class="btn btn-lg btn-primary" v-else>Save</button>
        <button type="button" class="btn btn-secondary btn-lg" @click="cancelHandler">Cancel</button>
    </section>
</template>

<script>
import vue2Dropzone from 'vue2-dropzone'
import 'vue2-dropzone/dist/vue2Dropzone.min.css'
import InputTag     from 'vue-input-tag'

export default {
    components : {
        vueDropzone : vue2Dropzone,
        InputTag
    },
    props      : {
        variants         : {
            type     : Array,
            required : true
        },
        edit_mode        : {
            type     : Boolean,
            required : false
        },
        product          : {
            type     : Object,
            required : false
        },
        product_variants : {
            type     : Object,
            required : false
        }
    },
    data() {
        return {
            product_name           : '',
            product_sku            : '',
            description            : '',
            images                 : [],
            product_variant        : [
                {
                    option : this.variants[0].id,
                    tags   : []
                }
            ],
            product_variant_prices : [],
            dropzoneOptions        : {
                url            : 'https://httpbin.org/post',
                thumbnailWidth : 150,
                maxFilesize    : 0.5,
                headers        : {"My-Awesome-Header" : "header value"}
            },
            error                  : false,
            success                : false,
            success_message        : '',
            error_message          : '',
        }
    },
    methods : {
        // it will push a new object into product variant
        newVariant() {
            let all_variants = this.variants.map(el => el.id)
            let selected_variants = this.product_variant.map(el => el.option);
            let available_variants = all_variants.filter(entry1 => !selected_variants.some(entry2 => entry1 == entry2))
            // console.log(available_variants)

            this.product_variant.push({
                option : available_variants[0],
                tags   : []
            })
        },

        // check the variant and render all the combination
        checkVariant() {
            let tags = [];
            this.product_variant_prices = [];
            this.product_variant.filter((item) => {
                tags.push(item.tags);
            })

            this.getCombn(tags).forEach(item => {
                this.product_variant_prices.push({
                    title : item,
                    price : 0,
                    stock : 0
                });
            })
        },

        // combination algorithm
        getCombn(arr, pre) {
            pre = pre || '';
            if (!arr.length) {
                return pre;
            }
            let self = this;
            let ans = arr[0].reduce(function (ans, value) {
                return ans.concat(self.getCombn(arr.slice(1), pre + value + '/'));
            }, []);
            return ans;
        },

        // image upload handler
        imageUploadHandler(file) {
            if (file.manuallyAdded === false) {
                this.images.push(file.dataURL);
            }
        },

        // store product into database
        saveProduct() {
            let product = {
                title                  : this.product_name,
                sku                    : this.product_sku,
                description            : this.description,
                product_image          : this.images,
                product_variant        : this.product_variant,
                product_variant_prices : this.product_variant_prices
            }


            axios.post('/product', product).then(response => {
                console.log(response.data);
                if (response.data.success === true) {
                    this.success = true;
                    this.success_message = response.data.message;
                }
            }).catch(error => {
                if (error.response.data.hasOwnProperty('errors')) {
                    this.error = true;
                    for (let err in error.response.data.errors) {
                        this.error_message = error.response.data.errors[err][0];
                    }
                } else {
                    this.error = true;
                    this.error_message = error.response.data.message;
                }
            }).finally(() => {
                this.product_name = '';
                this.product_sku = '';
                this.description = '';
                this.images = [];
                this.product_variant = [
                    {
                        option : this.variants[0].id,
                        tags   : []
                    }
                ];
                this.product_variant_prices = [];
                this.error = false;
                this.error_message = '';
                this.$refs.myVueDropzone.removeAllFiles();
            });

            console.log(product);
        },

        // update product into database
        updateProduct() {
            let product = {
                title                  : this.product_name,
                sku                    : this.product_sku,
                description            : this.description,
                product_image          : this.images,
                product_variant        : this.product_variant,
                product_variant_prices : this.product_variant_prices
            }


            axios.put('/product/' + this.product.id, product)
                 .then(response => {
                     console.log(response.data);
                     if (response.data.success === true) {
                         this.success = true;
                         this.success_message = response.data.message;
                     }
                 }).catch(error => {
                if (error.response.data.hasOwnProperty('errors')) {
                    this.error = true;
                    for (let err in error.response.data.errors) {
                        this.error_message = error.response.data.errors[err][0];
                    }
                } else {
                    this.error = true;
                    this.error_message = error.response.data.message;
                }
            });

            console.log(product);
        },

        // Cancel button handler
        cancelHandler() {
            location.replace('/product');
        },

        // Load edit data from database
        loadEditData() {
            // Product data set
            this.product_name = this.product.title;
            this.product_sku = this.product.sku;
            this.description = this.product.description;

            // Product image set
            this.product.product_images.forEach(item => {
                var file = {
                    size : item.file_size,
                    name : item.file_name,
                    type : item.mime_type
                };
                var url = '/storage/' + item.file_path;
                this.$refs.myVueDropzone.manuallyAddFile(file, url);
            });

            // Product variant data set
            let new_variants = [];
            for (let index in this.product_variants) {
                let new_item = {
                    option : index,
                    tags   : []
                };
                this.product_variants[index].forEach(item => {
                    new_item.tags.push(item.variant);
                });
                new_variants.push(new_item);
            }
            this.product_variant = new_variants;
            this.checkVariant();

            // Product variant price data set
            this.product_variant_prices.map((item, index) => {
                item.price = this.product.product_variant_prices[index].price;
                item.stock = this.product.product_variant_prices[index].stock;
                return item;
            });
        }


    },
    mounted() {
        console.log('Component mounted.')

        // If edit mode is true then load all edit data from database
        if (this.edit_mode === true) {
            this.loadEditData();
        }
    }
}
</script>
