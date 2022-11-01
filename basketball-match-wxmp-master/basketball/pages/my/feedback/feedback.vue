<template>
	<view>
		<textarea v-model="content" placeholder="请输入要反馈的内容"></textarea>
		
			<button  class="m-t-40 mx-btn bg858 white f36" @click="post">提交反馈</button>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				content:''
			}
		},
		methods: {
			post(){
				let _this=this;
				this.$api.suggest({content:this.content}).then(res=>{
					console.log(res)
					
					  this.$util.errorToShow('提交成功')
					  setTimeout(function(){
					  		_this.$util.navigateBack(1)
					  },1000)
					  
				})
			}
		}
	}
</script>

<style>
	textarea{
		background-color: #fff;
		width: 690rpx;
		margin: 30rpx;
		/* color: #; */
		padding: 30rpx;
		box-sizing: border-box;
	}

</style>
