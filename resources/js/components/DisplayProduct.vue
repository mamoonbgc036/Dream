<template>
    <div class="card">
    <form action="" class="card-header">
        <div class="form-row justify-content-between">
            <div class="col-md-2">
                <input type="text" name="title" v-model="selectedProduct" placeholder="Product Title" class="form-control">
            </div>
            <div class="col-md-2">
                <select name="variant" id="" class="form-control" v-model="selectedVariant">
                    <option v-for="varies in variants" :value="varies">{{ varies }}</option>
                </select>
            </div>

            <div class="col-md-3">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">Price Range</span>
                    </div>
                    <input type="text" v-model="priceFrom" name="price_from" aria-label="First name" placeholder="From"
                        class="form-control">
                    <input type="text" v-model="priceTo" name="price_to" aria-label="Last name" placeholder="To" class="form-control">
                </div>
            </div>
            <div class="col-md-2">
                <input type="date" name="date" v-model="selectedDate" placeholder="Date" class="form-control">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary float-right" @click.prevent="fetchAllProducts"><i class="fa fa-search"></i></button>
            </div>
        </div>
    </form>

    <div class="card-body">
        <div class="table-response">
            <table class="table">
                <loading :active.sync="isLoading"
                 :can-cancel="true"
                 :on-cancel="onCancel"
                 :is-full-page="fullPage"></loading>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Variant</th>
                        <th width="100px">Action</th>
                    </tr>
                </thead>

                <tbody>

                    <tr v-for="(product,index) in products.data" :key="product.id">
                        <td>{{ index+1 }}</td>
                        <td id="name">{{ product.title }}<br> Created at : {{product.created_at | formatDate }}</td>
                        <td id="des">{{ product.description }}</td>
                        <td>
                          <div :class="product.active ? 'reduce':''" >
                            <dl v-for="(varies, index) in product.product_variants_price" :key="varies.id" class="row mb-0" style="height: 80px; overflow: hidden" id="variant">
                            <dt class="col-sm-3 pb-0">
                                <p>{{product.product_variants.find(dat => dat.id === varies.product_variant_one)}}</p>
                                <!-- <p> {{ product.product_variants.find(dat => dat.id == varies.product_variant_one)}}/{{ product.product_variants.find(dat => dat.id == varies.product_variant_two)}}{{ varies.product_variant_three ? '/'+product.product_variants.find(dat => dat.id === varies.product_variant_three) : '' }}</p> -->
                            </dt>
                            <dd class="col-sm-9">
                                <dl class="row mb-0">
                                    <dt class="col-sm-4 pb-0">Price : {{ varies.price }} </dt>
                                    <dd class="col-sm-8 pb-0">InStock : {{ varies.stock }} </dd>
                                </dl>
                            </dd>
                            </dl>
                          </div>
                            <button @click="toggle(index)" class="btn btn-sm btn-link">{{ product.active ? 'show more':'show less' }}</button>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a :href="`/product/${ product.id }/edit`" class="btn btn-success">Edit</a>
                            </div>
                        </td>
                    </tr>

                </tbody>

            </table>
            <pagination :data="products" @pagination-change-page="fetchAllProducts"></pagination>
        </div>

    </div>

    <div class="card-footer">
        <div class="row justify-content-between">
            <div class="col-md-6">
                <p>Showing {{ products.from }} to {{ products.to }} out of {{ products.total }}</p>
            </div>
            <div class="col-md-2">

            </div>
        </div>
    </div>
    <router-view></router-view>
</div>
</template>
<script>
    // Import component
    import Loading from 'vue-loading-overlay';
    // Import stylesheet
    import 'vue-loading-overlay/dist/vue-loading.css';
import '../../css/custom.css';
export default {
  data() {
    return {
      products:{},
      selectedVariant:"",
      selectedProduct:"",
      priceFrom:"",
      priceTo:"",
      selectedDate:"",
      isLoading:false,
      fullPage: true,
      variants:[],  
    };
  },
  components: {
            Loading
        },
  filters: {
    formatDate(value) {
      const date = new Date(value)
      const options = { year: 'numeric', month: 'short', day: 'numeric' }
      return date.toLocaleDateString('en-US', options)
    }
  },
  mounted() {
    this.fetchAllProducts();
  },
  methods: {
    fetchAllProducts(page=1) {
        // this.isLoading= true;
      axios
        .get(`/all?page=${page}&name=${this.selectedProduct}&price_from=${this.priceFrom}&price_to=${this.priceTo}&variant=${this.selectedVariant}&created_at=${this.selectedDate}`,)
        .then((response) => {
            const test =  response.data.data.map(element=>{
               return {...element, active:false}
            })

            response.data.data = test;
            this.products = response.data;
            console.log(this.products);
            // this.isLoading=false;
            const myVar = response.data.data.map(element=>{
                 return element.product_variants.map(items=>{
                    return items.variant
                 })
            });
            this.variants = [...new Set(myVar.flat())];
            // this.getVariants();
        })
        .catch((error) => console.log(error));
    },
    // getVariants(){
    //     axios.get('/variants')
    //     .then(response=>{
    //         const varArry = response.data.map(ele=>{
    //             return ele.variant
    //         });
    //         this.variants =  [...new Set(varArry)];
    //     }).catch((errors)=>{
    //         console.log(errors);
    //     })
    // },
    toggle(index) {
      this.products.data[index].active = !this.products.data[index].active;
    },
  },
};
</script>

