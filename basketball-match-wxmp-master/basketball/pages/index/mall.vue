<template>
	<view class="p-lr-30">
		<view>
			<uni-search-bar v-model="title" placeholder="搜索商品" clearButton="always" cancelButton="none"
				@confirm="search" @clear="clear" />
				
				 <uni-segmented-control :current="current" :values="items" @clickItem="onClickItem" styleType="button" activeColor="#4cd964"></uni-segmented-control>
				
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				title: '',
				 items: ['???ҹ???', '?ֽ?????'],
				        current: 0

			}
		},
		onLoad() {
			let _this = this
		},
		methods: {
			search(e) {
				this.title = e
			},
			clear() {
				this.clear = ''
			},
			navigateTo(url) {
				console.log(url)
				uni.navigateTo({
					url: url
				})
			}

		}
	}
</script>

<style>

</style>
