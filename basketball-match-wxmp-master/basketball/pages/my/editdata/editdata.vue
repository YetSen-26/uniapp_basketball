<template>
	<view class="p-lr-40">
		<view class="form-line">
			<view class="form-line-border row alignitems just-btw">
				<view class="form-line-left white">头像</view>
				<view class="form-line-right" @click="uploadImage()">
					<image v-if="userinfo.img_path" :src="userinfo.img_path"
						style="width: 100rpx;height: 100rpx;border-radius: 50%;"></image>
					<view v-else style="color: #007AFF;">上传</view>
				</view>
			</view>
			<view class="form-line-border  row alignitems just-btw">
				<view class="form-line-left white">手机号</view>
				<view class="form-line-right" style="color: #FFFFFF;">{{userinfo.mobile}}</view>
			</view>
			<view class="form-line-border  row alignitems just-btw">
				<view class="form-line-left white">姓名</view>
				<input class="form-line-right" v-model="userinfo.nickname" placeholder="请输入姓名"
					style="color: #FFFFFF;" />
			</view>
			<view class="form-line-border  row alignitems just-btw">
				<view class="form-line-left white">身份证号</view>
				<input class="form-line-right" v-model="userinfo.idcard" placeholder="请输入身份证号" type="idcard"
					style="color: #FFFFFF;" />
			</view>
			<view class="form-line-border row alignitems just-btw">
				<view class="form-line-left white">生日</view>
				<uni-datetime-picker type="date" class="form-line-right" :clear-icon="false" :value="userinfo.birthday"
					:border="false" @change="changeBirthday" />
			</view>
			<view class="form-line-border  row alignitems just-btw">
				<view class="form-line-left white">身高(cm)</view>
				<input class="form-line-right" v-model="userinfo.height" placeholder="请输入身高(cm)" type="number"
					style="color: #FFFFFF;" />
			</view>
			<view class="form-line-border  row alignitems just-btw">
				<view class="form-line-left white">体重(kg)</view>
				<input class="form-line-right" v-model="userinfo.weight" placeholder="请输入体重(kg)" type="number"
					style="color: #FFFFFF;" />
			</view>
			<view class="form-line-border  row alignitems just-btw">
				<view class="form-line-left white">上衣尺寸</view>
				<radio-group @change="radioChange">
					<view class="mx-flex" style="width: 460rpx;flex-wrap: wrap;">
						<block class="uni-list-cell uni-list-cell-pd" v-for="(item, index) in itemscc" :key="item">

							<radio :value="item" :checked="userinfo.t_shirt_size === item" />

							<text class="white m-r-30">{{item}}</text>
						</block>
					</view>
				</radio-group>

				<!-- <input class="form-line-right" v-model="userinfo.weight" placeholder="请输入体重(kg)" type="number" style="color: #FFFFFF;" /> -->
			</view>
			<view class="form-line-border  row alignitems just-btw">
				<view class="form-line-left white">高校</view>
				<input class="form-line-right" v-model="userinfo.school" placeholder="请输入高校" style="color: #FFFFFF;" />
			</view>
			<view class="form-line-border  row alignitems just-btw">
				<view class="form-line-left white">简介</view>
				<input class="form-line-right" v-model="userinfo.desc" placeholder="请输入简介" style="color: #FFFFFF;" />
			</view>


		</view>
		<button class="form-btn" @click="save">保存</button>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				itemscc: ['S', 'M', 'L', 'XL', 'XXL'],
				userinfo: {
					img_path: '',
					nickname: '',
					birthday: '',
					weight: '',
					height: '',
					desc: '',
					school: ''
				},
			}
		},
		onShow() {
			this.getuser()
		},
		methods: {
			radioChange(e) {
				console.log(e.detail.value)
				this.userinfo.t_shirt_size = e.detail.value;
			},
			getuser() {
				this.$api.authme().then(res => {
					this.userinfo = res.data;
					console.log("个人信息", this.userinfo)
				})
			},
			changeBirthday(e) {
				this.userinfo.birthday = e
			},
			uploadImage() {
				this.$api.uploadImage(1, this.picturecallback);
			},
			picturecallback(e) {
				console.log("图片回调获取的信息", e)
				this.userinfo.img_path = e;
			},
			save() {
				if (!this.userinfo.nickname) {
					uni.showToast({
						title: '请填写姓名',
						icon: 'none'
					})
					return
				}
				if (!this.userinfo.img_path) {
					uni.showToast({
						title: '请上传头像',
						icon: 'none'
					})
					return
				}
				if (!this.userinfo.birthday) {
					uni.showToast({
						title: '请选择生日',
						icon: 'none'
					})
					return
				}
				if (!this.userinfo.weight) {
					uni.showToast({
						title: '请输入体重',
						icon: 'none'
					})
					return
				}
				if (!this.userinfo.height) {
					uni.showToast({
						title: '请输入身高',
						icon: 'none'
					})
					return
				}
				if (!this.userinfo.school) {
					uni.showToast({
						title: '请输入高校',
						icon: 'none'
					})
					return
				}
				this.$api.editUser(this.userinfo).then(res => {
					console.log(res)
					// this.$storage.set('userinfo', this.userinfo)
					uni.showToast({
						title: '修改成功',
						icon: 'none'
					})
					setTimeout(() => {
						uni.switchTab({
							url: '/pages/my/my'
						})
					}, 1000);
				})
			}
		}
	}
</script>

<style>
	.form-btn {
		/* width: 690rpx; */
		height: 94rpx;
		line-height: 94rpx;
		background: #f00206;
		border-radius: 10rpx;
		/* margin-left: 30rpx; */
		color: #fff;
		font-size: 32rpx;
		margin-top: 66rpx;
	}

	.my-radio {
		font-size: 30rpx;
		margin-right: 20rpx;
	}

	radio {
		transform: scale(0.7);
	}

	.form-line-left {
		font-size: 32rpx;
	}

	.form-line-right {
		font-size: 30rpx;
		text-align: right;
	}

	.form-line {
		margin-top: 20rpx;
		/* background-color: #FFFFFF; */
	}

	.form-line-border {
		width: 100%;
		height: 121rpx;
		border-bottom: 1px solid #F5F5F5;
		padding: 0 33rpx;
		box-sizing: border-box;
	}
</style>
