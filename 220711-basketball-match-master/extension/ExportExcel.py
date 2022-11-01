import json
import logging

from openpyxl.utils import get_column_letter
from sqlalchemy import create_engine
import pandas as pd
import mysql.connector
import sys
from dotenv import dotenv_values
from logging import handlers
import numpy as np

envs = dotenv_values('../.env')
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

fileWrite = handlers.TimedRotatingFileHandler('../storage/logs/py_export_log', when='D', interval=12, backupCount=30,
                                              encoding='utf-8')
fileWrite.suffix = "%Y-%m-%d.log"
formatter = logging.Formatter("[%(asctime)s] %(levelname)s (line:%(lineno)d): %(message)s")
fileWrite.setFormatter(formatter)

console = logging.StreamHandler()
console.setLevel(logging.INFO)

logger.addHandler(fileWrite)
logger.addHandler(console)


def getMysqlConn():
    engine = create_engine(
        "mysql+mysqlconnector://{}:{}@{}:{}/{}".format(envs['DB_USERNAME'], envs['DB_PASSWORD'], envs['DB_HOST'],
                                                       envs['DB_PORT'], envs['DB_DATABASE']), max_overflow=5)
    return engine


def main():
    logger.info("********exports begin********")
    engine = getMysqlConn()
    engine.execute(f"update excel_export_logs set status = -1,msg='failed' where status=1 and retry_cnt>3")
    logger.info("清除异常执行进程")

    sql_query = 'select * from excel_export_logs where status = 0 limit 1;'
    df_read = pd.read_sql_query(sql_query, engine)
    if df_read.empty:
        engine.dispose()
        logger.info("暂无执行程序")
        logger.info("********exports end********")
        sys.exit()

    curRow = ''
    try:
        for row in df_read.iterrows():
            curRow = row[1]
            # 锁定表
            logger.info("准备写入数据")

            engine.execute(
                f"update excel_export_logs set status = 1,retry_cnt = {curRow['retry_cnt'] + 1} where id = {curRow['id']}")
            query_content = json.loads(curRow['query_content'])

            whereCondition = f""
            if curRow['from'] == 'collect':
                whereCondition += f" uniform_social_credit_code in (select uniform_social_credit_code from collect where user_id = '{curRow['user_id']}') AND "
            else:
                if len(query_content) > 0:
                    if 'year' in query_content.keys():
                        if query_content['year'] is not None:
                            whereCondition += f" create_date like '{query_content['year']}%' AND "

                    if 'province' in query_content.keys():
                        if query_content['province'] is not None:
                            whereCondition += f" province_name like '{query_content['province']}%' AND "

                    if 'industry_involved' in query_content.keys():
                        if query_content['industry_involved'] is not None:
                            whereCondition += f" industry_involved like '{query_content['industry_involved']}%' AND "

                    if 'registration_status' in query_content.keys():
                        if query_content['registration_status'] is not None:
                            whereCondition += f" registration_status like '{query_content['registration_status']}%' AND "

                    if 'company_type' in query_content.keys():
                        if query_content['company_type'] is not None:
                            whereCondition += f" company_type like '{query_content['company_type']}%' AND "

                    if 'key' in query_content.keys():
                        if query_content['key'] is not None:
                            if query_content['key_range'] in query_content.keys() & query_content['key_range'] is not None:
                                whereCondition += f" ({query_content['key_range']} like '%key%' OR legal_person_name like '%{query_content['key']}%') AND"
                            else:
                                whereCondition += f" (company_name like '%key%' OR legal_person_name like '%{query_content['key']}%') AND "

            sql_query = f"SELECT company_name,registration_status,legal_person_name,registration_money" \
                        f",real_registration_money,create_date,check_date,work_date_limit,province_name" \
                        f",city_name,area_name,uniform_social_credit_code,taxpayer_registration_number" \
                        f",registration_code,organizing_code,CBZZ,company_type,industry_involved,used_name" \
                        f",address,year_address,web_uri,tel,danger_tel,more_tel,email,more_email,business_scope" \
                        f" FROM company WHERE {whereCondition}  status=1 AND 1=1 "
            logger.info(sql_query)
            # print(sql_query)
            # engine.dispose()
            # sys.exit()
            logger.info("数据即将载入")
            df_read = pd.read_sql_query(sql_query, engine)
            logger.info("数据已载入")
            xlsFilepath = r"../public/exports/" + curRow['file_name']
            writer = pd.ExcelWriter(xlsFilepath, engine='xlsxwriter')
            df_read.to_excel(writer, index=False,
                             header=(['公司名称', '经营状态', '法定代表人', '注册资本', '实缴资本', '成立日期', '核准日期', '营业期限', '所属省份'
                                 , '所属城市', '所属区县', '统一社会信用代码', '纳税人识别号', '注册号', '组织机构代码'
                                 , '参保人数', '公司类型', '所属行业', '曾用名', '注册地址', '最新年报地址', '网址', '可用电话'
                                 , '不可用电话', '其他号码', '邮箱', '其他邮箱', '经营范围', ]))
            logger.info("数据已写入")
            workbook = writer.book
            worksheet = writer.sheets['Sheet1']
            for i, col in enumerate(df_read.columns):
                column_len = df_read[col].astype(str).str.len().max()
                column_len = max(column_len, len(col)) + 2
                worksheet.set_column(i, i, column_len)
            writer.save()
            logger.info("格式化完成")
            engine.execute(
                f"update excel_export_logs set status = 2 , msg='success' where id = {curRow['id']}")
            logger.info("索引表更新完毕")
    except Exception:
        logger.exception("导出失败!")
        if curRow['retry_cnt'] + 1 > 3:
            logger.exception("该记录无法再次重试")
            engine.execute(f"update excel_export_logs set status = -1,msg='failed' where id='{curRow['id']}'")
        else:
            logger.exception("未满3次，回滚记录")
            engine.execute(f"update excel_export_logs set status = 0 where id='{curRow['id']}'")

    engine.dispose()
    logger.info("********exports end********")


#
if __name__ == '__main__':
    main()
