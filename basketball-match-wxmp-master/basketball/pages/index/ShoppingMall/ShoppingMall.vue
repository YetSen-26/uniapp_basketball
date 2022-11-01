<template>
	<view class="p-lr-30">
		<view>
			<uni-search-bar v-model="param.query" placeholder="搜索" clearButton="always" cancelButton="none"
				@confirm="search" bgColor="#3D4154" @clear="clear" />

			<view style="width: 300rpx;">
				<uni-segmented-control :current="current" :values="items" @clickItem="onClickItem" styleType="button"
					activeColor="#E92934"></uni-segmented-control>
			</view>

			<view class="mx-flex mx-flex-between mx-flex-wrap">
				<view @click="navigateTo('/pages/index/ShoppingMall/details/details?id='+item.id)"
					class="mx-line m-t-24" v-for="(item,index) in info" :key="index">
					<image :src="item.cover_path" style="width: 300rpx;height: 300rpx;"></image>
					<view class="white m-t-30 two-text" style="height: 72rpx;">
						{{item.goods_name}}
					</view>
					<view class="mx-flex mx-flex-between mx-flex-model m-t-18">
						<!-- <view class="mx-label white f22">线下专属</view> -->
						<view class="white">金币 {{item.price}}</view>
					</view>
				</view>

			</view>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				items: ['金币购买', '现金购买'],
				current: 0,
				info: [],
				param: {
					query: '',
					type: 'gold',
					page: 1,
					pageSize: 10,
				}

			}
		},
		onLoad() {
			let _this = this
			this.postgoodslist();
		},
		methods: {
			onClickItem(e) {
				console.log(e)
				this.info = []
				this.current = e.currentIndex;
				this.param.type = e.currentIndex == 0 ? 'gold' : 'cash'
				this.param.page = 1;
				this.postgoodslist();
			},
			postgoodslist() {
				this.$api.goodslist(this.param).then(res => {
					console.log('商城', res)
					if (!res.data.length) {
						this.$util.errorToShow('暂时没有更多数据了哦')
						return;
					}
					this.info = [...this.info, ...res.data]
					console.log(this.info)
				})
			},
			search(e) {
				console.log(e)
				this.param.query = e.value
				this.info = []
				this.param.page = 1;
				this.postgoodslist();
			},
			clear() {
				this.param.query = ''
				this.info = []
				this.param.page = 1;
				this.postgoodslist();
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
	.mx-line {
		width: 330rpx;
		padding: 15rpx;
		box-sizing: border-box;
		background: #213075;
	}

	.mx-label {
		background-color: #F93944;
		padding: 3rpx 14rpx;
	}
</style>
