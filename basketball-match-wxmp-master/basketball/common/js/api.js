import * as util from './util.js' //引入util
import * as Storage from './storage.js' //引入storage

let apiBaseUrl = "https://api.dxsyqb.cn/" //域名更换的地方http://basketball-match.fvfire.com/


export var imageurl = apiBaseUrl
//需要form传值的
const apiPathsHasheader = ["api/auth/decryptData",'api/goods/order-store'];
// 不需要错误提示的接口
const apinotishi = ["api/project/grade"]

// 需要登录的，都写到这里，否则就是不需要登录的接口
const apiPathsHasToken = [];
const post = (apiPath, data, method = "POST", type) => {
	return new Promise((resolve, reject) => {
		if (type != "notips") {
			uni.showLoading({
				title: '加载中'
			});
		}

		let header = {
			'Accept': 'application/json',
			'Content-Type': 'application/json',
			'Access-Control-Allow-Origin': '*'
		}

		if (apiPathsHasheader.indexOf(apiPath) > -1) {
			header = {
				'Accept': 'application/json',
				'Content-Type': 'application/json',
				'Content-Type': 'application/x-www-form-urlencoded', //自定义请求头信息
				'Access-Control-Allow-Origin': '*',
			}
		}

		let userToken = Storage.get("token");
		let tokenType = Storage.get("tokenType");
		if (!userToken) {
		} else {
			header['Auth-Token'] = userToken;
			header['Authorization'] = tokenType + ' ' + userToken
		}
		uni.request({
			url: apiBaseUrl + apiPath,
			data: data,
			header: header,
			method: method,
			success: (response) => {
				if (type != "notips") {
					uni.hideLoading();
				}

				const result = response.data;
				console.log(result)
				// 登录信息过期或者未登录
				if (result.code == 200) {
					resolve(result);
				} else if (result.code === "401" || result.code === 401||result.status_code ==401) {
					showToastlogin(apiPath, data, method);
				} else {
					if (apinotishi.indexOf(apiPath) == -1) {
						reject(result);
						util.errorToShow(result.msg || "信息访问失败，请重试！")
					} else {
						resolve(result);
					}
				}


			},
			complete: () => {
				uni.hideLoading();
			},
			fail: (error) => {
				// #ifndef H5
				// let ua = navigator.userAgent.toLowerCase();
				// if (ua.match(/MicroMessenger/i) != "micromessenger") {
				// reject(401);
				// }
				uni.hideLoading();
				if (error && error.errmsg) {
					util.errorToShow(error.errmsg)
				}
			},
		});
	});
}

export function showToastlogin(apiPath, data, method) {
	Storage.del("token");
	Storage.del("tokenType");
	uni.showToast({
		title: '请先登录',
		icon: 'none',
		duration: 1000,
		complete: function() {
			setTimeout(function() {
				uni.hideToast();
				// 如果是小程序进入授权登录页面
				uni.navigateTo({
					url: '/pages/login/login',
					animationType: 'pop-in',
					animationDuration: 200
				});
			}, 1000)
		}
	});
}

// 上传图片
export const uploadImage = (num, callback) => {

	let apiPath = 'fileuploadFiles'
	let header = {
		'Accept': 'application/json',
		'Content-Type': 'multipart/form-data',
	}
	let userToken = Storage.get("token");
	let tokenType = Storage.get("tokenType");
	if (!userToken) {
	} else {
		header['Auth-Token'] = userToken;
		header['Authorization'] = tokenType + ' ' + userToken
	}
	uni.chooseImage({
		count: num,
		sizeType: ['compressed'], // 可以指定是原图还是压缩图，默认二者都有
		sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
		success: (res) => {
			uni.showLoading({
				title: '上传中...'
			});
			let tempFilePaths = res.tempFilePaths

			for (var i = 0; i < tempFilePaths.length; i++) {
				uni.uploadFile({
					url: apiBaseUrl + 'api/common/upload',
					filePath: tempFilePaths[i],
					fileType: 'image',
					name: 'file',
					header: header,
					success: (uploadFileRes) => {
						console.log(uploadFileRes)
						let data = JSON.parse(uploadFileRes.data);
						console.log(data)
						if (data.code==200) {
							callback(data.data.show_url);
						} else {
							util.errorToShow(result.message || "信息访问失败，请重试！")
						}
					},
					fail: (error) => {
						if (error && error.resultMsg) {
							showError(error.resultMsg);
						}
					},
					complete: () => {
						setTimeout(function() {
							uni.hideLoading();
						}, 250);
					},
				});
			}
		}
	});
}


const showError = error => {
	let errorMsg = ''
	switch (error.errno) {
		case 400:
			errorMsg = '请求参数错误'
			break
		case 401:
			errorMsg = '未授权，请登录'
			break
		case 403:
			errorMsg = '跨域拒绝访问'
			break
		case 404:
			errorMsg = `请求地址出错: ${error.config.url}`
			break
		case 408:
			errorMsg = '请求超时'
			break
		case 500:
			errorMsg = '服务器内部错误'
			break
		case 501:
			errorMsg = '服务未实现'
			break
		case 502:
			errorMsg = '网关错误'
			break
		case 503:
			errorMsg = '服务不可用'
			break
		case 504:
			errorMsg = '网关超时'
			break
		case 505:
			errorMsg = 'HTTP版本不受支持'
			break
		default:
			errorMsg = error.errmsg
			break
	}

	uni.showToast({
		title: errorMsg,
		icon: 'none',
		duration: 1000,
		complete: function() {
			setTimeout(function() {
				uni.hideToast();
			}, 1000);
		}
	});
}
// export const authlogin = (data, callback) => post('notauthentication/auth/login', data);
// export const cabinetqueryOneDetail = (data, callback) => post('cabinet/queryOneDetail', data, 'get');

export const competitionlist = (data, callback) => post('api/match/competition-list', data); //赛事列表

export const competitionapplylist = (data, callback) => post('api/match/competition-apply-list', data); //赛事列表

export const authlogin = (data, callback) => post('api/auth/login', data); //登录
export const authme = (data, callback) => post('api/auth/me', data); //获取个人信息
export const authData = (data, callback) => post('api/auth/decryptData', data); //解析微信用户信息
export const editUser = (data, callback) => post('api/auth/editUser', data); //修改用户信息
export const competitionapply = (data, callback) => post('api/match/competition-apply', data); //赛事报名
export const competitioncategory = (data, callback) => post('api/match/competition-category', data); //赛事分类
export const matchlist = (data, callback) => post('api/match/match-list', data); //赛事分类
export const guessingshow = (data, callback) => post('api/match/guessing-show', data); //赛程详情
export const matchshow = (data, callback) => post('api/match/match-show', data); //比赛详情
export const rankingcategory = (data, callback) => post('api/user/ranking-category', data); //排行榜类型
export const rankinglist = (data, callback) => post('api/user/ranking-list', data); //排行榜列表
export const signin = (data, callback) => post('api/system/sign-in', data); //签到
export const avgdata = (data, callback) => post('api/user/avg-data', data); //生涯-场均数据
export const history = (data, callback) => post('api/user/history', data); //生涯-历史数据
export const suggest = (data, callback) => post('api/system/suggest', data); //反馈
export const goodslist = (data, callback) => post('api/goods/list', data); //商品列表
export const goodsshow = (data, callback) => post('api/goods/show', data); //商品详情
export const orderstore = (data, callback) => post('api/goods/order-store', data); //商品下单
export const contractlist = (data, callback) => post('api/user/contract-list', data); //商品下单
export const goldlog = (data, callback) => post('api/system/gold-log', data); //金币日志
export const orderlist = (data, callback) => post('api/goods/order-list', data); //订单列表
export const ordershow = (data, callback) => post('api/goods/order-show', data); //订单详情
export const initializeData = (data, callback) => post('api/common/initializeData', data); //赛程详情
export const guessingstore = (data, callback) => post('api/match/guessing-store', data); //竞猜-创建	
export const matchcategory = (data, callback) => post('api/match/match-category', data); //比赛分类
export const rankingshow = (data, callback) => post('api/user/ranking-show', data); //比赛分类
export const bindMobile = (data, callback) => post('api/auth/bindMobile', data); //绑定手机号
