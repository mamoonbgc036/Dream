<template>
    <div class="card">
    <form action="" method="get" class="card-header">
        <div class="form-row justify-content-between">
            <div class="col-md-2">
                <input type="text" name="title" placeholder="Product Title" class="form-control">
            </div>
            <div class="col-md-2">
                <select name="variant" id="" class="form-control">
                    <option value="">test</option>
                </select>
            </div>

            <div class="col-md-3">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">Price Range</span>
                    </div>
                    <input type="text" name="price_from" aria-label="First name" placeholder="From"
                        class="form-control">
                    <input type="text" name="price_to" aria-label="Last name" placeholder="To" class="form-control">
                </div>
            </div>
            <div class="col-md-2">
                <input type="date" name="date" placeholder="Date" class="form-control">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary float-right"><i class="fa fa-search"></i></button>
            </div>
        </div>
    </form>

    <div class="card-body">
        <div class="table-response">
            <table class="table">
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
                        <td>{{ product.id }}</td>
                        <td id="name">{{ product.title }}<br> Created at : {{product.created_at | formatDate }}</td>
                        <td id="des">{{ product.description }}</td>
                        <td>
                          <div :class="product.active ? 'reduce':''" >
                            <dl v-for="(varies, index) in product.product_variants_price" :key="varies.id" class="row mb-0" style="height: 80px; overflow: hidden" id="variant">
                            <dt class="col-sm-3 pb-0">
                                <p> {{ product.product_variants.find(dat => dat.id === varies.product_variant_one).variant}}/{{ product.product_variants.find(dat => dat.id === varies.product_variant_two).variant}}</p>
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
                                <a href="#" class="btn btn-success">Edit</a>
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
</div>
</template>
<script>
import '../../css/custom.css';
export default {
  data() {
    return {
      products:{},
    };
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
      axios
        .get(`/all?page=${page}`)
        .then((response) => {
            const test =  response.data.data.map(element=>{
               return {...element, active:false}
            })

            response.data.data = test;
            this.products = response.data;
            console.log(this.products);
        })
        .catch((error) => console.log(error));
    },
    toggle(index) {
      this.products.data[index].active = !this.products.data[index].active;
    },
  },
};
</script>
