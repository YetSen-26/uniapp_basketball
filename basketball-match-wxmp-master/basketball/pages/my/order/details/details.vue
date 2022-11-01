<template>
	<view class="p-lr-30">
		<view class="mx-commodity mx-flex m-t-24">
			<image :src="info.goods.cover_path" style="width: 150rpx;height: 150rpx;"></image>
			<view class="m-l-30">
				<view class="white f30">{{info.goods.goods_name}}</view>
				<view class="m-t-60 f24 white">￥{{info.goods.price}} <text class="m-l-30 white">x{{info.num}}</text> </view>
			</view>
		</view>
		<view class="white">
			<text class="white">订单编号：</text>
			{{info.order_no}}
		</view>
		
		<view class="white m-t-20">
			<text class="white">商品类型：</text>
			{{info.type=="gold"?'积分商品':'购买商品'}}
		</view>
		
		<view class="white m-t-20">
			<text class="white">商品总价：</text>
			{{info.total_amount}}
		</view>
		
		
		
	</view>
</template>

<script>
	export default {
		data() {
			return {
				info:{},
			}
		},
		onLoad(option) {
			this.$api.ordershow({id:option.id}).then(res=>{
				console.log(res)
				this.info=res.data
			})
		},
		methods: {
			
		}
	}
</script>

<style>

</style>
