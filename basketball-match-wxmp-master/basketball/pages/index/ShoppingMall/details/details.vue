<template>
	<view>
		<image :src="info.cover_path" style="width: 100%;" mode="widthFix"></image>
		<view class="p-lr-30">
			<view class="f48 white">{{info.goods_name}} <text class="f24 white m-l-30">￥{{info.price}}</text></view>
			<view class="m-t-40 f40 white">商品详情</view>
			<view class="m-t-20">
				 <u-parse :content="info.content"></u-parse>
			</view>
		</view>
		
		<button class="m-t-40 mx-btn bg858 white f36" @click="postorderstore">{{info.type=='gold'?'积分兑换':'购买'}}</button>
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
		onLoad(option) {
			this.$api.goodsshow({id:option.id}).then(res=>{
				this.info=res.data
			})
		},
		methods: {
			postorderstore(){
				let _this=this;
				this.$api.orderstore({goods_id:this.info.id,num:1}).then(res=>{
					console.log(res)
					if(this.info.type=='cash'){
						this.requestPayment(res.data.sign)
					}else{
						_this.$util.errorToShow('购买成功，等待客服联系你')
							// _this.$storage.set('userinfo',res.data)
							setTimeout(function(){
									_this.$util.navigateBack(1)
							},1500)
					}
				})
			},
			requestPayment(info){
				let _this=this;
				uni.requestPayment(
				{
				"appId":info.appId,
				"timeStamp":info.timeStamp,
				"nonceStr":info.nonceStr,
				"package": info.package,
				"signType":info.signType,
				"paySign":info.paySign,
				"success":function(res){
					console.log(res)
				_this.$util.errorToShow('购买成功，等待客服联系你')
					// _this.$storage.set('userinfo',res.data)
					setTimeout(function(){
							_this.$util.navigateBack(1)
					},1500)
				},
				"fail":function(err){
					_this.$util.errorToShow("已取消支付")
				},
				"complete":function(res){}
				})
			}
			
		}
	}
</script>

<style>
.mx-btn{
	width: 100%;
	position: fixed;
	bottom: 0;
	left: 0;
	border-radius: 0;
}
</style>
