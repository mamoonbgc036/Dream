<template>
    <section>
        <div class="row">
            <div class="col-md-6">
                <loading :active.sync="isLoading"
                 :can-cancel="true"
                 :on-cancel="onCancel"
                 :is-full-page="fullPage"></loading>
                 <!-- <div class="bg-warning" v-for="error in errors">
                    {{ error }}
                 </div> -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="">Product Name</label>
                            <input type="text" v-model="product_name" placeholder="Product Name" class="form-control">
                            <!-- <p class="text-danger">{{ errors.title ? errors.title[0] : '' }}</p> -->
                        </div>
                        <div class="form-group">
                            <label for="">Product SKU</label>
                            <input type="text" v-model="product_sku" placeholder="Product Name" class="form-control">
                            <!-- <p class="text-danger">{{ errors.sku ? errors.sku[0] : '' }}</p> -->
                        </div>
                        <div class="form-group">
                            <label for="">Description</label>
                            <textarea v-model="description" id="" cols="30" rows="4" class="form-control"></textarea>
                            <!-- <p class="text-danger">{{ errors.description ? errors.description[0] : '' }}</p> -->
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Media</h6>
                    </div>
                    <div class="card-body border">
                        <vue-dropzone ref="imageDropzone" id="image-dropzone" :options="dropzoneOptions"></vue-dropzone>
                        <!-- <dropzone ref="imageDropzone" id="image-dropzone"></dropzone> -->
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
                                    <label v-if="product_variant.length != 1" @click="product_variant.splice(index,1), checkVariant"
                                           class="float-right text-primary"
                                           style="cursor: pointer;">Remove</label>
                                    <label v-else for="">Name</label>
                                    <input-tag v-model="item.tags" @input="checkVariant" class="form-control"></input-tag>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer" v-if="product_variant.length < variants.length && product_variant.length < 3">
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
                                <tr>
                                    <!-- <p class="text-danger">{{ errors.product_variant_prices ? errors.product_variant_prices[0] : '' }}</p> -->
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

        <button @click="saveProduct" type="submit" class="btn btn-lg btn-primary">Save</button>
        <button type="button" class="btn btn-secondary btn-lg">Cancel</button>
        <div class="alert alert-primary my-2" v-if="isSuccess">
            Insert Successfully
        </div>
    </section>
</template>

<script>
import vue2Dropzone from 'vue2-dropzone'
import 'vue2-dropzone/dist/vue2Dropzone.min.css'
import InputTag from 'vue-input-tag'

import Loading from 'vue-loading-overlay';
    // Import stylesheet
import 'vue-loading-overlay/dist/vue-loading.css';

export default {
    components: {
        vueDropzone: vue2Dropzone,
        InputTag,
        Loading
        // Dropzone
    },
    props: {
        variants: {
            type: Array,
            required: true
        }
    },
    data() {
        return {
            product_name: '',
            product_sku: '',
            description: '',
            isLoading: false,
            isSuccess: false,
            images: {},
            errors: {},
            product_variant: [
                {
                    option: this.variants[0].id,
                    tags: []
                }
            ],
            product_variant_prices: [],
            dropzoneOptions: {
                url: '/product',
                thumbnailWidth: 150,
                maxFilesize: 2,
                acceptedFiles: 'image/*',
                autoProcessQueue: false,
                addRemoveLinks: true,
                dictDefaultMessage: 'Drop files here or click to upload',
                headers: {
                'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content
                }
            }
        }
    },
    methods: {
         // it will push a new object into product variant
        newVariant() {
            let all_variants = this.variants.map(el => el.id)
            let selected_variants = this.product_variant.map(el => el.option);
            let available_variants = all_variants.filter(entry1 => !selected_variants.some(entry2 => entry1 == entry2))

            this.product_variant.push({
                option: available_variants[0],
                tags: []
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
                    title: item,
                    price: 0,
                    stock: 0
                })
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

        // store product into database
        async saveProduct() {
            this.isLoading = true;
            let product = {
                title: this.product_name,
                sku: this.product_sku,
                description: this.description,
                product_variant: this.product_variant,
                product_variant_prices: this.product_variant_prices
            };         
            await axios.post('/product', product).then(response => {
                console.log(response.data)
                this.saveImage(response.data)
            }).catch(error => {
                this.isLoading = false;
                //this.errors = error.response.data.errors;
                this.errors = error.response.data.errors
                console.log(this.errors);
            })
        },
        saveImage(id){
            const formData = new FormData()
            // const files = this.$refs.imageDropzone.getAcceptedFiles()
            let dropzone = this.$refs.imageDropzone.dropzone;
            let files = dropzone.files;
            console.log(files);

            // Append each selected file to the FormData
            for (let i = 0; i < files.length; i++) {
                const file = files[i]
                formData.append('images[]', file)
            }

            formData.append('id',id);
            axios.post('/product_image', formData).then(response => {
                console.log(response.data+'test');
                this.isLoading= false;
                this.isSuccess = true;
                console.log(response.data);
            }).catch(error => {
                this.errors = error.response.data.errors
            })
        }


    },
    mounted() {
    }
}
</script>
