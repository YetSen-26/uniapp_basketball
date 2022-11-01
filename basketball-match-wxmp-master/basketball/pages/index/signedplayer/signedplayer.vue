<template>
	<view>
		<image src="../../../static/home/6.png" style="width: 100%;" mode="widthFix"></image>
		<view class="p-lr-30 p-t-30">
			<view class="title f32">校园球星权益</view>
			<view class="mx-flex mx-flex-model m-t-50">
				<image src="../../../static/home/7.png" style="width: 84rpx;height: 84rpx;"></image>
				<view class="labelWidth512 m-l-20">
					<view class="f28 weight500">组织</view>
					<view class="c999 f22" style="line-height: 26rpx;">有机会加入大学生厂牌——山东Camel，寻找志同道合的朋友。</view>
				</view>
			</view>

			<view class="mx-flex mx-flex-model m-t-50">
				<image src="../../../static/home/8.png" style="width: 84rpx;height: 84rpx;"></image>
				<view class="labelWidth512 m-l-20">
					<view class="f28 weight500">变强</view>
					<view class="c999 f22" style="line-height: 26rpx;">提供丰富的赛事机会和专业的训练，提高其自身实力。</view>
				</view>
			</view>


			<view class="mx-flex mx-flex-model m-t-50">
				<image src="../../../static/home/9.png" style="width: 84rpx;height: 84rpx;"></image>
				<view class="labelWidth512 m-l-20">
					<view class="f28 weight500">扬名</view>
					<view class="c999 f22" style="line-height: 26rpx;">专业的团队、强大的自媒体提高曝光度，帮助其获得商业推广。
					</view>
				</view>
			</view>
			
			<view class="mx-flex mx-flex-model m-t-50">
				<image src="../../../static/home/7.png" style="width: 84rpx;height: 84rpx;"></image>
				<view class="labelWidth512 m-l-20">
					<view class="f28 weight500">装备</view>
					<view class="c999 f22" style="line-height: 26rpx;">定期提供赛事专属运动装备。</view>
				</view>
			</view>

			<view class="title f32 m-t-58">校园球星</view>
			<view class="mx-flex mx-flex-wrap">
				<view @click="selectindex=index" class="mx-text-center m-t-30 m-r-97" v-for="(item,index) in info" :key="index">
					<image :src="item.avatar_path" style="width: 100rpx;height: 100rpx;border-radius: 80rpx;"></image>
					<view class="m-t-10">{{item.name}}</view>
				</view>
				
			</view>
			<image :src="info[selectindex].cover_path" class="m-t-24" style="width: 100%;" mode="widthFix"></image>
			<view>
				<view class="f32 weight500">校园球星：{{info[selectindex].name}}<text class="m-l-20 p-lr-12"
						style="background-color: #EEEEEE;">{{info[selectindex].age}}岁/{{info[selectindex].height}}CM/{{info[selectindex].weight}}KG </text> </view>
			</view>
			<view class="m-tb-30">
               {{info[selectindex].desc}}
			</view>

			<view class="f32 weight500">历史战绩</view>
			
			<view class="mx-flex m-t-30 m-b-30">
				<view class="mx-th c999 f24">比赛时间 </view>
				<view class="mx-th c999 f24">站点</view>
				<view class="mx-th c999 f24">得分</view>
			</view>
			<view class=" p-b-84">
				<view class="mx-flex m-t-10" v-for="(item,index) in info[selectindex].grades">
					<view class="mx-th">{{item.c_date}}</view>
					<view class="mx-th">{{item.site}}</view>
					<view class="mx-th">{{item.grade}}</view>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
                info:{},
				selectindex:0,
			}
		},
		onLoad() {
		  this.$api.contractlist().then(res=>{
			  console.log("列表",res)
			  this.info=res.data;
		  })	
		},
		methods: {

		}
	}
</script>

<style>
	.mx-th{
		 overflow: hidden;
		    white-space: nowrap;
		    text-overflow: ellipsis;
	}
	.mx-th:nth-child(1){
		width: 200rpx;
	}
	.mx-th:nth-child(2){
		width: 390rpx;
	}
	.mx-th:nth-child(3){
		width: 100rpx;
	}
	page {
		background-color: #fff;
	}

	.title {
		border-left: 6rpx solid #E92933;
		padding-left: 16rpx;
		font-weight: bold;
	}

	.m-r-97:nth-child(4n) {
		margin-right: 0 !important;
	}
</style>
