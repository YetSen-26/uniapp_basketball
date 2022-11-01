import logging
from logging import handlers

import sqlalchemy
from sqlalchemy import create_engine
import pandas as pd
import mysql.connector
import sys
import time as current_t
from dotenv import dotenv_values

envs = dotenv_values('../.env')
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

fileWrite = handlers.TimedRotatingFileHandler('../storage/logs/py_import_log', when='D', interval=12, backupCount=30,
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
    logger.info("********import begin********")
    engine = getMysqlConn()
    engine.execute(f"update excel_import_logs set status = -1,msg='failed' where status=1")
    logger.info("清除异常执行进程")

    sql_query = 'select * from excel_import_logs where status = 0 limit 1;'
    df_read = pd.read_sql_query(sql_query, engine)
    if df_read.empty:
        engine.dispose()
        logger.info("暂无执行程序")
        logger.info("********import end********")
        sys.exit()

    file_id = df_read.iloc[0, :]['id']
    try:
        engine.execute(f"update excel_import_logs set status = 1,msg='success' where id={file_id}")
        file_name = df_read.iloc[0, :]['file_name']
        logger.info(f"更新索引表完毕 file_id:{file_id},file_name:{file_name}", )
        df = pd.read_excel('../storage/app/' + file_name, engine='openpyxl')
        df.columns = ['company_name', 'registration_status', 'legal_person_name', 'registration_money',
                      'real_registration_money', 'create_date'
            , 'check_date', 'work_date_limit', 'province_name', 'city_name', 'area_name', 'uniform_social_credit_code',
                      'taxpayer_registration_number', 'registration_code'
            , 'organizing_code', 'CBZZ', 'company_type', 'industry_involved', 'used_name', 'address', 'year_address',
                      'web_uri', 'tel', 'danger_tel', 'more_tel'
            , 'email', 'more_email', 'business_scope']
        logger.info("数据已载入")

        data = pd.DataFrame(df)
        data = data.dropna(axis=0, subset=['uniform_social_credit_code'])
        data = data.replace(r'^-$', '', regex=True)
        data = data.drop_duplicates(['uniform_social_credit_code'], ignore_index=True)
        logger.info("数据已清洗")

        data.to_sql(name='company', if_exists='append', con=engine, chunksize=5000, index=False)
        logger.info("数据写入完成")

        engine.execute(
            f"DELETE FROM company WHERE id NOT IN (select a.max_id from (SELECT max( id ) AS max_id  FROM company group by uniform_social_credit_code) a) ")
        logger.info("目标数据已清洗")

        engine.execute(f"update excel_import_logs set status = 2,msg='success' where id={file_id}")
        logger.info("索引表更新完毕")

        logger.info("开始更新字典表")
        time = int(current_t.time())
        dictArr = []

        years = engine.execute(f"select distinct DATE_FORMAT(`create_date`,'%Y') AS year from company where create_date != '' and create_date is not null order by year").fetchall()
        print(years)
        for year in years:
            dictArr.append(('year',year.year,time))

        industry_involveds = engine.execute(
            f"select distinct replace(industry_involved,'\t','') as industry_involved from company where industry_involved != '' and industry_involved is not null order by industry_involved").fetchall()
        for industry_involved in industry_involveds:
            dictArr.append(('industry_involved',industry_involved.industry_involved,time))

        province_names = engine.execute(
            f"select distinct replace(province_name,'\t','') as province_name from company where province_name != '' and province_name is not null order by province_name").fetchall()
        for province_name in province_names:
            dictArr.append(('province_name',province_name.province_name,time))

        registration_statuses = engine.execute(
            f"select distinct replace(registration_status,'\t','') as registration_status  from company where registration_status != '' and registration_status is not null order by registration_status").fetchall()
        for registration_status in registration_statuses:
            dictArr.append(('registration_status',registration_status.registration_status,time))

        company_types = engine.execute(
            f"select distinct replace(company_type,'\t','') as company_type from company  where company_type != '' and company_type is not null order by company_type").fetchall()
        for company_type in company_types:
            dictArr.append(('company_type',company_type.company_type,time))

        manySql = pd.DataFrame(dictArr)
        manySql.columns = ['name','value','created_at']
        # manySql.to_sql('')
        manySql.to_sql(name='dict', if_exists='append', con=engine, chunksize=5000, index=False,dtype={
                     'id': sqlalchemy.types.BigInteger(),
                     'name': sqlalchemy.types.String(length=20),
                     'value':  sqlalchemy.types.String(length=255),
                     'created_at': sqlalchemy.types.BigInteger()
                 })
        # engine.executemany(f"insert into dict values(name,value,created_at)(%,%,%)", dictArr)
        engine.execute(f"delete from dict where created_at != '{time}'")

        logger.info("结束更新字典表")
    except Exception:
        engine.execute(f"update excel_import_logs set status = -1,msg='failed' where id='{file_id}'")
        logger.exception("read excel failed!")

    engine.dispose()
    logger.info("********import end********")


#
if __name__ == '__main__':
    main()
