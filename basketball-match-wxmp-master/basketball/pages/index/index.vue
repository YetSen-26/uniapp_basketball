<template>
	<view>
		<image src="/static/bg.png" style="width: 750rpx;height: 448rpx;"></image>
		<view class="box" :style="{'minHeight':swiperheight+'rpx'}">
			<view class="myinfo">
				<image :src="info.img_path?info.img_path:'/static/logo.png'" style="width: 89rpx;height: 89rpx;border-radius: 50%;" mode="aspectFit">
				</image>
				<view style="font-size: 30rpx;font-weight: bold;color: #FFFFFF;margin-left: 26rpx;margin-right: 12rpx;">
					{{info.nickname?info.nickname:'匿名用户'}}
				</view>
				<view>
					<image src="/static/index/hi.png" style="width: 32rpx;height: 32rpx;"></image>
				</view>
			</view>
			<view class="banner-line">
				<view class="absolute">
					<image src="/static/index/ljbm.png" class="banner-img" style="width: 688rpx;height:230rpx;">
					</image>
					<view class="banner-left" @click="navigateTo('/pages/index/signup/signup')">
						<image src="/static/index/bm.png" style="height: 59rpx;width: 45rpx;margin-right: 44rpx;"
							mode="aspectFit">
						</image>
						<view class="banner-value">
							<view>立即报名</view>
							<view class="banner-illustrate">Sign up now</view>
						</view>
						<image src="/static/index/right.png" style="width: 15rpx;height: 27rpx;margin-left: 200rpx;">
						</image>
					</view>
				</view>
				<view class="absolute">
					<image src="/static/index/qyqy.png" class="banner-img" style="width: 688rpx;height:282rpx;">
					</image>
					<view class="banner-left" @click="navigateTo('/pages/index/signedplayer/signedplayer')">
						<image src="/static/index/left.png"
							style="width: 15rpx;height: 27rpx;margin:0 180rpx 0 120rpx;"></image>
						<image src="/static/index/qy.png" style="height: 80rpx;width: 61rpx;margin-left: 69rpx;"
							mode="aspectFit">
						</image>
						<view class="banner-value" style="margin-left: 20rpx;">
							<view>校园球星</view>
							<view class="banner-illustrate">Signed players</view>
						</view>
					</view>
				</view>
				<view class="absolute">
					<image src="/static/index/scgz.png" class="banner-img" style="width: 688rpx;height:230rpx;">
					</image>
					<view class="banner-left" @click="navigateTo('/pages/index/rule/rule')">
						<image src="/static/index/gz.png" style="height: 62rpx;width: 60rpx;margin-right: 44rpx;"
							mode="aspectFit">
						</image>
						<view class="banner-value">
							<view>赛程规则</view>
							<view class="banner-illustrate">Schedule Rules</view>
						</view>
						<image src="/static/index/right.png" style="width: 15rpx;height: 27rpx;margin-left: 200rpx;">
						</image>
					</view>
				</view>
				<view class="absolute">
					<image src="/static/index/sc.png" class="banner-img" style="width: 688rpx;height:230rpx;">
					</image>
					<view class="banner-left" @click="navigateTo('/pages/index/ShoppingMall/ShoppingMall')">
						<image src="/static/index/left.png" style="width: 15rpx;height: 27rpx;margin:0 0 0 120rpx;">
						</image>
						<view style="min-width: 180rpx;text-align: right;">{{info.gold_cnt>999?'999+':info.gold_cnt}}币
						</view>
						<image src="/static/index/mall.png" style="height: 62rpx;width: 67rpx;margin-left: 69rpx;"
							mode="aspectFit">
						</image>
						<view class="banner-value" style="margin-left: 20rpx;">
							<view>商城</view>
							<view class="banner-illustrate">Mall</view>
						</view>
					</view>
				</view>
			</view>
				<view class="banner-hb">
				<block v-for="(item,index) in listhb" :key="index">
					<view>{{item.length>6?(item.substring(0,5)+'...'):item}}</view>
					<view style="width: 200rpx;" v-if="index==2"></view>
				</block>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				info: {
					gold_cnt:0
				},
				listhb: ['市场合作伙伴', '市场合作伙伴市场合作伙伴', '市场合作伙伴', '市场合作伙伴市场合作伙伴', '市场合作伙伴', '市场合作伙伴', '市场合作伙伴', '市场合作伙伴',
					'市场合作伙伴',
					'市场合作伙伴', '市场合作伙伴', '市场合作伙伴', '市场合作伙伴', '市场合作伙伴', '市场合作伙伴', '市场合作伙伴'
				],
				num: 1000
			}
		},
		onShow() {
			let token = this.$storage.get('token')
			if (token) {
				this.postauthme();
			}
		},
		methods: {
			postauthme() {
				this.$api.authme().then(res => {
					console.log("个人信息", res)
					this.info = res.data;
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
					return
				}
			}
		}
	}
</script>

<style lang="scss">
	page {
		background-color: #171923;
	}

	.banner-hb {
		margin-top: 526rpx;
		padding: 0 44rpx;

		view {
			color: #ffffff;
			font-size: 20rpx;
			display: inline-block;
			margin-right: 30rpx;
			min-width: 130rpx;
		}
	}

	.banner-hb view:nth-child(1) {
		display: block;
	}

	.banner-value {
		width: 200rpx;
		text-align: center;
	}

	.banner-illustrate {
		margin-top: 8rpx;
		font-size: 20rpx;
	}

	.banner-line {
		margin: 6rpx 31rpx;
		position: relative;
	}


	.banner-left {
		padding: 0 39rpx;
		box-sizing: border-box;
		position: absolute;
		top: 0;
		left: 0;
		display: flex;
		// width: 688rpx;
		// height: 230rpx;
		align-items: center;

		padding-top: 78rpx;

		view {
			color: #ffffff;
		}
	}

	.banner-line .absolute {
		position: absolute;
		top: 124rpx;
	}

	.banner-line .absolute:nth-child(3) {
		top: 250rpx;
	}

	.banner-line .absolute:nth-child(4) {
		top: 376rpx;
	}

	.banner-line .absolute:first-child {
		top: 0;
	}

	.box {
		background: #171923;
		width: 750rpx;
		position: relative;
		bottom: 80rpx;
		border-radius: 50rpx 50rpx 0 0;
	}

	.myinfo {
		padding-top: 45rpx;
		margin-left: 60rpx;
		display: flex;
		height: 89rpx;
		line-height: 89rpx;
	}

	.zw {
		font-size: 32rpx;
		font-weight: bold;
		color: #FFFFFF;
	}

	.yw {
		font-size: 20rpx;
		font-weight: 500;
		color: #FFFFFF;
		padding-top: 13rpx;
	}
</style>
