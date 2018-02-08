<template>
  <nav v-cloak aria-label="Page navigation" class="text-center">
        <ul class="pagination" :style="{display: pageTotal > 1 ? 'inline-block' : 'none'}">
            <li :class="{disabled: currentPage === 1}">
                <a  @click="toPage(currentPage - 1)" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            <template v-if="pageTotal <= 10">
                <li  v-for="(page, key) in pageTotal" :class="{active: currentPage === page}" :key="key">
                    <a @click="toPage(page)">{{ page }} </a>
                </li>
            </template>
            <template v-if="pageTotal > 10">
                <li v-if="currentPage > 5"><a @click="toPage(1)">1</a></li>
                <li v-if="currentPage > 6"><a >...</a></li>
                 <li v-for="(page, key) in (currentPage + 5)" :class="{active: currentPage === page}" :key="key">
                    <a v-if="currentPage - page < 5 && page < pageTotal" @click="toPage(page)">{{ page }} </a>
                </li>
                <li v-if="currentPage <  pageTotal - 6"><a >...</a></li>
                <li :class="{active: currentPage === pageTotal}"><a @click="toPage(pageTotal)" >{{ pageTotal }}</a></li>
            </template>
            <li :class="{disabled: currentPage === pageTotal}">
                <a @click="toPage(currentPage+1)" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
            <li class="go-page fr">
                <input type="number" name="page" v-model="inputPage" class="form-control" @keyup.enter="toPage(Number(inputPage))">
                <button class="btn btn-success to" @click="toPage(Number(inputPage))" >跳转</button>
            </li>
        </ul>
    </nav>
</template>

<script>
export default {
  props: ['currentPage', 'pageTotal'],
    data() {
        return {
            inputPage: 1
        }
    },
    methods: {
        toPage(page) {
            if (page !== this.currentPage && page > 0 && page <= this.pageTotal) {
                this.$emit('update:currentPage', page);
                this.$emit('change', page);
            }
        }
    }
}
</script>
