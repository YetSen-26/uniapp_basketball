<template>
	<view class="p-lr-30 p-t-30">
		<view class="mx-line">
			<view class="title p-l-48 f30 white">{{info.competition_name}}</view>
			<view class="p-lr-28 ">
				<view class="mx-flex mx-flex-model p-tb-20">
					<image src="@/static/home/4.png" style="width: 22rpx;height: 22rpx;"></image>
					<view class="white m-l-26">比赛时间：{{info.begin_date}} 至 {{info.end_date}}</view>
				</view>

				<view class="mx-flex mx-flex-model p-tb-20">
					<image src="@/static/home/5.png" style="width: 22rpx;height: 22rpx;"></image>
					<view class="white m-l-26">报名费：{{info.entry_fee}}元 保证金：{{info.deposit_fee}}元</view>
				</view>

				<view class="mx-flex mx-flex-model p-tb-20">
					<image src="@/static/home/4.png" style="width: 22rpx;height: 22rpx;"></image>
					<view class="white m-l-26">截止报名日期：{{info.expiration_date}}</view>
				</view>

				<view class="mx-flex mx-flex-model p-tb-20">
					<image src="@/static/home/3.png" style="width: 19rpx;height: 24rpx;"></image>
					<view>
						<view class="white m-l-26">比赛场馆：{{info.venue_name}}</view>
						<view class="m-l-26 m-t-10 f26 C6C5">详细地址：{{info.venue_address}}</view>
					</view>

				</view>
			</view>
		</view>

		<view class="mx-fixed mx-flex mx-flex-center mx-flex-wrap">
			<checkbox-group @change="checkboxChange">
				<label class="C6C5 row">
					<checkbox value="0" :checked="istrue" color="red" style="transform:scale(0.7)" />
					<view class="row white">我已同意接受<view class="white" @click.stop="navigateTo(0)">《报名须知》</view>和<view
							class="white" @click.stop="navigateTo(1)">《比赛规则》</view>
					</view>
				</label>

			</checkbox-group>
			<button class="m-t-40 mx-btn bg858 white f36" @click="post">报名申请</button>
		</view>

	</view>
</template>

<script>
	export default {
		data() {
			return {
				info: {},
				istrue: false,
			}
		},
		onLoad() {
			this.info = this.$storage.get('eventdetails')
		},
		methods: {
			checkboxChange(e) {
				console.log(e)
				if (e.detail.value[0] == '0') {
					this.istrue = true
				} else {
					this.istrue = false
				}
			},
			navigateTo(type) {
				uni.navigateTo({
					url: '/pages/my/protocol?type=' + type
				})
			},
			post() {
				let _this = this;
				if (_this.istrue == false) {
					uni.showToast({
						title: '请同意须知和规则后提交',
						icon: 'none'
					})
					return
				}
				_this.$api.authme().then(res => {
					if (res.data.idcard) {
						_this.postcompetitionapply()
					}
					else{
						_this.$util.modelShow('提示','报名需填写身份证号,是否去完善',this.editnavigateTo)
					}
				})
			},
			editnavigateTo() {
				uni.navigateTo({
					url: '/pages/my/editdata/editdata'
				})
			},
			postcompetitionapply() {
				let _this = this
				_this.$api.competitionapply({
					competition_id: _this.info.id
				}).then(res => {
					let info = res.data.sign;
					if(res.data.status==0 && info){
						uni.requestPayment({
							"appId": info.appId,
							"timeStamp": info.timeStamp,
							"nonceStr": info.nonceStr,
							"package": info.package,
							"signType": info.signType,
							"paySign": info.paySign,
							"success": function(res) {
								console.log(res)
								_this.$util.errorToShow('支付成功')
								// _this.$storage.set('userinfo',res.data)
								setTimeout(function() {
									_this.$util.navigateBack(1)
								})
							},
							"fail": function(err) {
								_this.$util.errorToShow("已取消支付")
							},
							"complete": function(res) {}
						})
					}
					else if(res.data.status==1 && !info){
						_this.$util.errorToShow('报名成功')
					}
					else{
						_this.$util.errorToShow('数据错误')
					}
				})
			}
		}
	}
</script>

<style lang="scss">
	.mx-fixed {
		position: fixed;
		bottom: 40rpx;
		width: 750rpx;
		left: 0;
	}

	.mx-line {
		background-color: #212F74;

		.title {
			height: 80rpx;
			line-height: 80rpx;
			background: #E92933;
		}
	}
</style>
