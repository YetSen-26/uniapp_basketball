<template>
	<view class="page"
		style="background-image: url('/static/my/bjt.png');background-size: 750rpx 480rpx;background-repeat:no-repeat;">
		<view class="p-lr-40 m-t-224">
			<view v-if="show">
				<view class="row alignitems" style="height: 140rpx;">
					<image :src="info.img_path?info.img_path:'/static/logo.png'" class="imgView140 m-l-44"
						style="border-radius: 50%;" @click="navigateTo('/pages/my/editdata/editdata')"></image>
					<view class="m-l-44">
						<view class="f32 white bold" @click="navigateTo('/pages/my/editdata/editdata')">
							{{info.nickname?info.nickname:'完善信息'}}</view>
						<view class="f22 white bold m-t-32">{{info.school?info.school:''}}</view>
						<!-- <view class="f22 white bold m-t-32">{{info.school}}</view> -->
					</view>
					<view class="m-l-44" @click="!info.is_signed?postsignin():''" :class="info.is_signed?'yqd':'qd'">
						{{!info.is_signed?'签到':'已签到'}}
					</view>
				</view>
				<view class="m-t-82 row just-center">
					<!-- @click="navigateTo('/pages/my/goldcoins/goldcoins')" -->
					<view class="jb row just-btw alignitems p-lr-48">
						<view class="f30 bold white">我的金币</view>
						<view class="f48 bold white">{{info.gold_cnt}}</view>
					</view>
				</view>
			</view>
			<view v-else>
				<view class="row alignitems m-b-100" style="height: 140rpx;">
					<image src="" class="imgView140 m-l-44" style="border-radius: 50%;"></image>
					<view class="m-l-44">
						<view class="f32 white bold" @click="navigateTo('/pages/login/login')">去登录</view>
					</view>
				</view>
			</view>

			<view class="m-t-70">
				<!-- 				<view @click="navigateTo('/pages/my/order/order')" class="row just-btw alignitems" style="border-bottom: 1rpx solid #E5E5E5;height: 115rpx;">
					<view class="row">
						<image src="/static/my/czda.png" style="width: 36rpx;height: 32rpx;margin-right: 41rpx;">
						</image>
						<view class="f30 weight500 white">我的订单</view>
					</view>
				</view> -->

				<view class="row just-btw alignitems" style="border-bottom: 1rpx solid #E5E5E5;height: 115rpx;"
					@click="navigateTo('/pages/my/career')">
					<view class="row">
						<image src="/static/my/czda.png" style="width: 36rpx;height: 32rpx;margin-right: 41rpx;">
						</image>
						<view class="f30 weight500 white">生涯</view>
					</view>
				</view>


				<view @click="navigateTo('/pages/my/feedback/feedback')" class="row just-btw alignitems"
					style="border-bottom: 1rpx solid #E5E5E5;height: 115rpx;">
					<view class="row">
						<image src="/static/my/yjfk.png" style="width: 36rpx;height: 32rpx;margin-right: 41rpx;">
						</image>
						<view class="f30 weight500 white">意见反馈</view>
					</view>
				</view>
				<view class="row just-btw alignitems" style="border-bottom: 1rpx solid #E5E5E5;height: 115rpx;"
					v-if="show">
					<view class="row">
						<image src="/static/my/tc.png" style="width: 36rpx;height: 32rpx;margin-right: 41rpx;">
						</image>
						<view class="f30 weight500 white" @click="$api.showToastlogin">退出登录</view>
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
				info: {},
				show: true
			}
		},
		onShow() {
			let _this = this
			let token = this.$storage.get('token')
			if (token) {
				this.show = true
				this.postauthme();
			} else {
				this.show = false
			}
		},
		methods: {
			postauthme() {
				this.$api.authme().then(res => {
					console.log("个人信息", res)
					this.info = res.data;
				})
			},
			postsignin() {
				this.$api.signin().then(res => {
					console.log(res)
					this.$util.errorToShow('签到成功')
					this.postauthme();
				})
			},
			navigateTo(url) {
				console.log(url)
				let token = this.$storage.get('token')
				if (token) {
					uni.navigateTo({
						url: url
					})
				} else {
					uni.showToast({
						title: '请登录',
						icon: "none"
					})
					setTimeout(() => {
						uni.redirectTo({
							url: '/pages/login/login'
						})
					}, 1000)
				}
			}

		}
	}
</script>

<style>
	.main {
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
		overflow-y: scroll;
		overflow-x: hidden;
	}

	.jb {
		width: 600rpx;
		height: 98rpx;
		background: #212F74;
		border-radius: 49rpx;
	}

	.qd {
		width: 120rpx;
		height: 50rpx;
		background: #E92934;
		border-radius: 25rpx;
		font-size: 24rpx;
		font-weight: bold;
		color: #FFFFFF;
		text-align: center;
		line-height: 50rpx;
	}

	.yqd {
		width: 120rpx;
		height: 50rpx;
		background: #DCDCDC;
		border-radius: 25rpx;
		font-size: 24rpx;
		font-weight: bold;
		color: #999999;
		text-align: center;
		line-height: 50rpx;
	}
</style>
