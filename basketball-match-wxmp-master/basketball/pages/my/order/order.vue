<template>
	<view  class="p-lr-30">
		
		<view class="m-t-30" style="width: 300rpx;">
			<uni-segmented-control :current="current" :values="items" @clickItem="onClickItem" styleType="button"
				activeColor="#E92934"></uni-segmented-control>
		</view>
		
		<view>
			<view v-for="item in info" :key="index" class="mx-line" @click="navigateTo('/pages/my/order/details/details?id='+item.id)">
				<!-- <view class="mx-flex mx-flex-between "> -->
					<view class="white">订单编号：{{item.order_no}}</view>
					<view class="white">商品数量：{{item.total_amount}}</view>
				<!-- </view> -->
				
				<!-- <view class="mx-flex mx-flex-between m-t-10"> -->
					<view class="white">支付{{current==0?'金额':'积分'}}：{{item.total_amount}}</view>
					<view class="white">下单时间：{{item.created_at}}</view>
				<!-- </view> -->
			</view>
			
		</view>
		
		
	</view>
</template>

<script>
	export default {
		data() {
			return {
				info:[],
				items: ['金币购买', '现金购买'],
				current: 0,
				param:{
					pageSize:10,
					type:'gold',
					order_no:''
				},
			}
		},
		onLoad() {
			this.post();
		},
		onReachBottom(){
			this.param.page++;
			this.post();
		},
		methods: {
			navigateTo(url) {
				this.$util.navigateTo(url)
			},
			post(){
				this.$api.orderlist(this.param).then(res=>{
					console.log('订单',res)
					if(!res.data.length){
						   this.$util.errorToShow('暂无更多数据了哦')
					}
					this.info=[...this.info,...res.data]
					
				})
			},
			onClickItem(e){
				this.current=e.currentIndex;
				console.log(e.currentIndex)
			}
			
		}
	}
</script>

<style>
.mx-line{
	width: 690rpx;
	border-bottom: 1px solid #fff;
	padding: 12rpx 0;
	margin-top: 30rpx;
}
</style>
