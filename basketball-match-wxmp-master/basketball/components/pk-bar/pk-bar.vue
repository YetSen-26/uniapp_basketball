<template>
	<view class="pk-detail-con">
		<i class="lightning" :style="{ left: line+ '%' }"></i>
		<i class="acimgs" :style="{ left: line+ '%' }"></i>
		<view class="progress">
			<view class="progress-bar" :style="{ width: line + '%'}"></view>
			<span class="team-name team-l">{{ pkData.left }}人</span>
			<span class="team-name team-r">{{ pkData.right }}人</span>
		</view>
	</view>
</template>

<script>
	export default {
		name: "pk-bar",
		props:{
			pkData:{
				type:Object,
				default:()=>({})
			}
		},
		data() {
			return {
				line:50,
			};
		},
		mounted() {
				// let {	left,	right	} = this.pkData;
				// if (left >= right) {
				// 	this.line = (left /(right + left) * 100 ).toFixed(2);
				// } else {
				// 	this.line = (100 - (right / (right + left) * 100)).toFixed(2);
				// }
		}
	}
</script>

<style lang="less">
.pk-detail-con {
		position: relative;
		margin: 0 auto 0;
	}

	.progress {
		width: 100%;
		height: 30rpx;
		overflow: hidden;
		border-radius: 20rpx;
		background-color: #3B59E1;
		// background: linear-gradient(-142deg, #FF0000, #E58F8C);
	}

	.progress-bar {
		position: relative;
		height: 30rpx;
		text-align: left;
		background-color: #E82D33;
		// background: linear-gradient(-135deg, #00D6FF, #0024FF);
		
	}

	.progress,
	.progress-bar {
		position: relative;
	}

	.lightning {
		display: none;
		position: absolute;
		top: -40rpx;
		z-index: 9;
		width: 70rpx;
		height: 140rpx;
		float: right;
		margin-right: -12rpx;
		background: url("./progress.png") no-repeat center;
		background-size: cover;
		transform: translateX(-20rpx);
	}
	.acimgs{
		position: absolute;
		bottom:0;
		width: 58rpx;
		height: 30rpx;
		z-index: 9;
			background-color: #3B59E1;
		// background: linear-gradient(-135deg, #58e3ff, #0fb9ff);
		clip-path: polygon(0 0, 70% 0, 25% 100%, 0% 100%);
		transform: translateX(-10rpx);
	}
	.team-name {
		position: absolute;
		top: 0;
		line-height: 30rpx;
		color: #f3eee1;
		letter-spacing: 1rpx;
		font-size: 24rpx;
	}

	.team-l {
		left: 23rpx;
	}

	.team-r {
		right: 23rpx;
	}

</style>
