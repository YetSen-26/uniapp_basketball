<template>
	<view>
		<view class="mx-flex mx-flex-between mx-flex-model p-lr-30 head p-t-40">
			<view class="title f32 bold">最强大学生</view>
			<!-- 	<uni-search-bar v-model="title" placeholder="搜索球员" clearButton="always" cancelButton="none"
				@confirm="search" @clear="clear" bgColor="#3D4154" /> -->

		</view>
		<view class="p-lr-30 m-t-18">
			<view class="mx-flex mx-flex-model mx-flex-between">

				<picker @change="bindPickerChange" :value="index" range-key="ranking_name" :range="typelist">
					<view class="bgfff mx-flex mx-flex-model p-tb-25 p-lr-36 radius36">
						{{index==-1?'请选择类型':typelist[index].ranking_name}}
						<image src="../../static/schedule/9.png" class="m-l-10" style="width: 18rpx;height: 10rpx;">
						</image>
					</view>
				</picker>

				<!-- 	<view class="bgfff radius50 mx-flex">
					<view @click="FileListbtn(index)" :class="Fileindex==index?'bg000 white':'bgfff c999'"
						class=" radius36  p-tb-25 p-lr-54" v-for="(item,index) in FileList" :key="index">{{item}}</view>
				</view> -->
			</view>
		</view>
		<!-- </view> -->
		<view>
			<view class="mx-line3" v-for="(item,index) in info" :key="index"  @click="navigateTo('/pages/my/career?id='+item.id)">
					<view class="mx-flex mx-flex-model">
						<image v-if="index<3"
							:src="index==0?'../../static/schedule/5.png':index==1?'../../static/schedule/6.png':'../../static/schedule/7.png'"
							style="width: 70rpx;height: 60rpx;"></image>
						<view v-else class="mx-text-center" style="width: 75rpx;">{{index+1}}.</view>
						<view style="width: 97rpx;">
							<image class="m-l-14 radius50" :src="item.avatar_path?item.avatar_path:'/static/logo.png'"
								style="width: 97rpx;height: 97rpx;border-radius: 50%;"></image>
						</view>
						<view class="m-l-20">
							<view class="f30 bold">{{item.person_name}} <text
									class="c999 f28">（{{item.team_name}}）</text></view>
							<view class="f30 c666 m-t-16 f28">{{item.tag}}</view>
						</view>
					</view>
					<view class="mx-flex">
						<!--  {{Math.floor(Number(item.grade)/20)}} 向下取整  -->
						<image src="../../static/schedule/8.png" v-for="a in Math.floor(Number(item.grade)/20)"
							style="width: 40rpx;height: 38rpx;margin-right: 13rpx;"></image>
					</view>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		components: {},
		data() {
			return {
				index: 0,
				typelist: [],
				Fileindex: 0,
				// FileList: ['济南', '全省'],
				param: {
					ranking_id: '',
					page: 1,
					pageSize: 12,
				},
				info: [],
			}
		},
		onLoad(option) {
			this.postrankingcategory(); //类型
			this.postrankinglist(); //列表
		},
		onReachBottom() {
			this.param.page++;
			this.postrankinglist();
		},
		methods: {
			postrankinglist() {
				this.$api.rankinglist(this.param).then(res => {
					console.log(res)
					if (!res.data.length) {
						this.$util.errorToShow('暂无更多数据了哦')
					}
					this.info = [...this.info, ...res.data]
				})
			},
			bindPickerChange(e) {
				this.param.page = 1
				this.info = []
				this.index = e.detail.value;
				this.param.ranking_id = this.typelist[e.detail.value].id;
				this.postrankinglist();
			},
			postrankingcategory() {
				this.$api.rankingcategory().then(res => {
					console.log('类型', res)
					this.typelist = res.data;
			 })
			},
			FileListbtn(e) {
				this.Fileindex = e;
			},
			navigateTo(url) {
				this.$util.navigateTo(url)
			},
		}
	}
</script>

<style lang="scss">
	.mx-line3 {
		margin-top: 20rpx;
		background-color: #fff;
		display: flex;
		justify-content: space-between;
		align-items: center;
		height: 160rpx;
		padding: 0 10rpx 0 16rpx;
		box-sizing: border-box;
	}

	.mx-line3:nth-child(1) {
		background: linear-gradient(-85deg, #FEE2B9 0%, #F5EFE3 100%);
	}

	.mx-line3:nth-child(2) {
		background: linear-gradient(-85deg, #ABAFBD 0%, #F4F4F4 100%);
	}

	.mx-line3:nth-child(3) {
		background: linear-gradient(-85deg, #D4B396 0%, #F4F4F4 100%);
	}

	.uni-searchbar__box {
		background-color: #fff !important;
		border-radius: 50rpx !important;
	}

	.title {
		border-left: 6rpx solid #E92933;
		padding-left: 12rpx;
		height: 33rpx;
		line-height: 33rpx;
	}

	.head {
		background-color: #F8F8F8;
	}

	page {
		background-color: #F4F4F4;
	}

	.mx-line {
		width: 686rpx;
		height: 355rpx;
		padding: 76rpx 20rpx 0 20rpx;
		box-sizing: border-box;
		margin-bottom: 80rpx;

		.img {
			position: relative;
			top: -40rpx;
		}
	}

	.mx-line-center {
		padding: 30rpx 50rpx;
	}

	checkbox {
		transform: scale(0.56);
	}
</style>
