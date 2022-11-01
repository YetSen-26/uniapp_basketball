<template>
	<view>
		<view v-for="item in info" class="mx-line">
			<view class="mx-flex mx-flex-between ">
				<view class="white">{{item.balance}}</view>
				<view class="white">{{item.gold_cnt}}</view>
			</view>
			
			<view class="mx-flex mx-flex-between m-t-10">
				<view class="white">{{item.from}}</view>
				<view class="white">{{item.created_at}}</view>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				param:{
					page:1,
					pageSize:10,
				},
				info:[]
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
			post(){
				this.$api.goldlog(this.param).then(res=>{
					console.log(res)
					if(!res.data.length){
						   this.$util.errorToShow('暂无更多数据了哦')
					}
					this.info=[...this.info,...res.data];
				})
			}
		}
	}
</script>

<style>
.mx-line{
	width: 690rpx;
	margin-left: 30rpx;
	border-bottom: 1px solid #fff;
	padding: 12rpx 0;
}
</style>
