<template>
	<view class="p-lr-40">
		<view class="m-t-24 row just-center">
			<image :src="info.img_path?info.img_path:info.avatar_path?info.avatar_path:'/static/logo.png'"
				style="width: 140rpx;height: 140rpx;border-radius: 50%;" mode=""></image>
		</view>
		<view class="m-t-18 row just-center f30 weight500 white">{{info.person_name?info.person_name:info.nickname}}
		</view>
		<view class="m-t-18 row just-center f22 weight500 c999">年龄{{info.age?info.age:infocj.age?infocj.age:0}}岁 /
			身高{{info.height?info.height:infocj.height?infocj.height:0}}cm</view>
		<view class="m-t-32 row just-center">
			<view class="box row just-btw alignitems p-lr-40">
				<view>
					<view class="f40 bold white textcenter">{{info.cnt?info.cnt:infocj.total_cnt?infocj.total_cnt:0}}
					</view>
					<view class="f22 weight500 white textcenter">场次</view>
				</view>

				<view class="xian"></view>

				<view>
					<view class="f48 bold white textcenter">
						{{info.total_grade?info.total_grade:infocj.sum_pointer?infocj.sum_pointer:0}}</view>
					<view class="f22 weight500 white textcenter">总得分</view>
				</view>

				<view class="xian"></view>

				<view>
					<view class="f40 bold white textcenter">
						{{info.avg_grade?info.avg_grade:infocj.avg_pointer?infocj.avg_pointer:0}}</view>
					<view class="f22 weight500 white textcenter">场均得分</view>
				</view>

			</view>
		</view>
		<view class="m-t-100">
			<view>
				<uni-segmented-control :current="current" :values="items" @clickItem="onClickItem" styleType="text"
					activeColor="#FFFFFF"></uni-segmented-control>

				<view class="mx-table">
					<view class="bg858 mx-flex m-t-16">
						<view class="mx-th">日期</view>
						<view class="mx-th">对手球队</view>
						<view class="mx-th">得分</view>
						<view class="mx-th">篮板</view>
						<view class="mx-th">助攻</view>
					</view>
					<view class="bg9E1 p-b-30">
						<!-- v-for="(item,index) in infols" :key="index" -->
						<view class="mx-flex" v-for="(item,index) in infols" :key="index">
							<view class="mx-th mx-td">{{item.c_date}}</view>
							<view class="mx-th mx-td">{{item.team_name?item.team_name:item.rel_team_name}}</view>
							<view class="mx-th mx-td">{{item.total_pointer?item.total_pointer:item.grade?item.grade:0}}
							</view>
							<view class="mx-th mx-td">{{item.rebound_cnt}}</view>
							<view class="mx-th mx-td">{{item.assist_cnt}}</view>
						</view>



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
				id: '',
				title: '',
				items: ['场均得分'],
				current: 0,
				info: {},
				infocj: {},
				infols: [],
				param: {
					page: 1,
					pageSize: 12,
				},
			}
		},
		onLoad(option) {
			if (option.id) {
				this.id = option.id;
				this.postrankingshow();
				return
			}
			let _this = this
			this.postauthme();
			this.postavgdata();
			this.posthistory();
		},
		onReachBottom() {
			if (!this.id) {

			}
			this.param.page++;
			this.posthistory();
		},
		methods: {
			postrankingshow() {
				this.$api.rankingshow({
					ranking_person_id: this.id
				}).then(res => {
					console.log('排行榜', res)
					this.info = res.data[0];
					this.infols = res.data[0].ranking_person_grade;
				})
			},
			onClickItem(e) {
				this.param.page == 1;
				this.current = e.currentIndex;
				this.infols = []
				this.posthistory()
			},
			postauthme() {
				this.$api.authme().then(res => {
					console.log("个人信息", res)
					this.info = res.data;
				})

			},
			postavgdata() {
				this.$api.avgdata().then(res => {
					console.log('场均数据', res)
					this.infocj = res.data;
				})
			},
		 posthistory() {
				this.$api.history(this.param).then(res => {
					console.log('历史统计', res)
					if (!res.data.length) {
						this.$util.errorToShow('暂无更多数据了哦')
					}
					this.infols = [...this.infols, ...res.data]
				})
			},
			search(e) {
				this.title = e
			},
			clear() {
				this.clear = ''
			},
			navigateTo(url) {
				console.log(url)
				uni.navigateTo({
					url: url
				})
			}

		}
	}
</script>

<style>
	.segmented-control__text {
		color: #818181 !important;
	}

	.segmented-control__item--text {
		color: #ffffff !important;
	}

	.box {
		width: 684rpx;
		height: 114rpx;
		background: #213075;
	}

	.xian {
		width: 1rpx;
		height: 68rpx;
		background: #3550C8;
	}

	.mx-th {
		color: #ffffff;
		padding: 6rpx 6rpx;
		box-sizing: border-box;
		text-align: center;
		overflow: hidden;
	}

	.mx-td {
		padding-top: 28rpx;
		display: flex;
		justify-content: center;
		align-items: center;
	}

	.mx-th:nth-child(1) {
		width: 200rpx;
	}

	.mx-th:nth-child(2) {
		width: 180rpx;
	}

	.mx-th:nth-child(3) {
		width: 100rpx;
	}

	.mx-th:nth-child(4) {
		width: 100rpx;
	}

	.mx-th:nth-child(5) {
		width: 100rpx;
	}
</style>
