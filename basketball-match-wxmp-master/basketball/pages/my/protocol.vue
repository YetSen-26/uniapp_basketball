<template>
	<view class="p-lr-28">
		<view class="m-t-20">
			<u-parse :content="info"></u-parse>
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
				info: '',
			}
		},
		onLoad(option) {
			this.$api.initializeData().then(res => {
				console.log(res)
				if (option.type == 0) {
					uni.setNavigationBarTitle({
						title: '报名须知'
					});
					this.info = res.data.apply_rule;
				} else {
					uni.setNavigationBarTitle({
						title: '比赛规则'
					});
					this.info = res.data.schedule_rule;
				}
			})
		},
		methods: {

		}
	}
</script>

<style>
	page {
		background-color: #fff;
	}
</style>
