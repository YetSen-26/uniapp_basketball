<template>
	<view>
		<image class="login" src="@/static/logo.png" style="width: 192rpx;height: 192rpx;"></image>
		<view class="login-title yh-margin-top-m yh-text-center">篮球赛事</view>
		<view class="yh-info">
			<view class="main">登录后开发者将获得以下权限</view>
			<view class="explain"><text>.</text>获取你的公开信息（昵称、头像、地区及性别）</view>
		</view>
		<!-- 		<view class="yh-text-center yh-margin-top-big yh-font-size-s yh-flex yh-flex-model yh-flex-center">
			<view class="yh-font-size-s yh-flex yh-flex-model" @click="agreement=!agreement">
				<radio :checked="agreement" /> 我已阅读
			</view>
		
			<text style="color: #3681C4;" @click="$util.navigateTo('/pages/login/agreement/agreement')">
				小猪嘉美用户注册和服务协议</text>
		</view>
 -->

		<button class="yh-btn  yh-margin-autolr" style="margin-top:30rpx;" open-type="getPhoneNumber"
			@getphonenumber="getphonenumber">确认登录</button>


	</view>
</template>

<script>
	// import phoneCode from '@/components/authorizedphone.vue'
	export default {
		components: {
			// phoneCode
		},
		data() {
			return {
				code: "获取验证码",
				visibility: false,
				state: '',
				agreement: true,
			}
		},
		onShow() {
			this.getCode();
		},
		onLoad() {

		},
		methods: {
			getphonenumber(e) {
				console.log('eeeeeeeeee', e)
				if (e.detail.errMsg == 'getPhoneNumber:fail:user deny') {
					uni.showToast({
						title: '未授权登录',
						icon: "none"
					})
					return
				}
				let _this = this;
				if (_this.code) {
					_this.bindGetUserInfo(e)
				} else {
					_this.$util.errorToShow('未取得code');
					_this.getcode()
				}
			},
			bindGetUserInfo(e) {
				let _this = this;
				// _this.getUserProfile();

				_this.$api.authlogin({
					code: _this.code
				}).then(res => {
					console.log(res)
					_this.$storage.set('token', res.data.access_token)
					_this.$storage.set('tokenType', res.data.token_type)
					_this.authData(e)
					// _this.getUserProfile();
					// _this.$util.errorToShow('登录成功')
					// setTimeout(function(){
					// 		_this.$util.navigateBack(1)
					// })

				})
			},
			authData(e) {
				let _this = this;
				let param = {
					encryptedData: e.detail.encryptedData,
					iv: e.detail.iv,
					// code: _this.code,
				}
				_this.$api.authData(param).then(res => {
					console.log(res)
					let info = res.data;
					_this.$api.bindMobile({
						mobile: info.phoneNumber
					}).then(res => {
						console.log(res)
					})

					_this.$util.errorToShow('登录成功')
					// _this.$storage.set('userinfo', res.data)
					setTimeout(function() {
						uni.switchTab({
							url: '/pages/index/index'
						})
					})
				})
			},
			getCode: function() {
				let _this = this;
				uni.login({
					provider: 'weixin',
					success: function(res) {
						console.log("code信息", res)
						if (res.code) {
							_this.code = res.code ? res.code : res.authCode;
						} else {
							_this.$util.errorToShow('未取得code');
						}
					},
					fail: function(res) {
						// _this.$util.errorToShow('用户授权失败wx.login');
					}
				});
			},
		}

	}
</script>

<style lang="scss">
	.yh-btn {
		width: 690rpx;
		margin-left: 30rpx;
		margin-top: 60rpx !important;
		background-color: #E92933;
		color: #fff;
	}

	radio {
		-webkit-transform: scale(0.6);
		transform: scale(0.6);
	}

	.login-title {
		font-size: 36rpx;
		font-weight: bold;
		color: #FFF;
		text-align: center;
	}

	.yh-info {
		width: 630rpx;
		margin-left: 60rpx;
		border-top: 1px solid #E7E9EF;
		margin-top: 70rpx;
		padding-top: 30rpx;

		.main {
			font-size: 32rpx;
			font-weight: 500;
			color: #fff;
		}

		.explain {
			// margin-top:40rpx;
			color: #949AA0;
		}

		text {
			font-size: 66rpx;
			position: relative;
			top: -4rpx;
			margin-right: 6rpx;
		}
	}

	.login {
		display: block;
		// border-radius: 120rpx;
		margin: 100rpx auto 30rpx auto;
	}

	.yh-btn {
		box-shadow: 0 0 0 0 !important;
	}

	.yh-login-line {
		width: 590rpx;
		margin: 0 auto;

		.remarks {
			font-size: 28rpx;
		}

		.title {
			color: #3681C4;
			font-size: 44rpx;
		}

		.form {
			margin-top: 100rpx;
		}

		//登录表单
		.yh-form-item {
			width: 100%;
			height: 88rpx;
			font-size: 26rpx;
			border-bottom: 1px solid #C7C7C7;

			image {
				position: absolute;
				top: 30rpx;
				left: 0;

			}

			.yh-input {
				padding-left: 36rpx;
				width: 400rpx;
				height: 88rpx;
			}

			.yh-code {
				color: #3681C4;
				line-height: 88rpx;
			}
		}
	}
</style>
