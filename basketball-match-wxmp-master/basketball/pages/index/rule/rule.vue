<template>
	<view class="p-lr-28">
		<view class="m-t-20">
			 <u-parse :content="info.schedule_rule"></u-parse>
		</view>
	</view>
</template>

<script>
		import uParse from '@/components/u-parse/u-parse.vue'
	export default {
		components: {
		  uParse
		},
		data() {
			return {
				info:{}
			}
		},
		onLoad() {
			this.$api.initializeData().then(res=>{
				console.log(res)
				this.info=res.data;
			})
		},
		methods: {
			
		}
	}
</script>

<style>
page{
	background-color: #fff;
}
</style>
