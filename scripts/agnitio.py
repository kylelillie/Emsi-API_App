#!/usr/bin/python

import requests
import json
import pandas as pd
import zipfile
import sys
import os
from os.path import basename
import re
from datetime import datetime

try:
	import zlib
	compression = zipfile.ZIP_DEFLATED
except:
	compression = zipfile.ZIP_STORED

	
def send_mail(send_to, file):

	try:
		import smtplib
		import yagmail
		alias = 'EDT Emsi Query'
		yag = yagmail.SMTP({'imap.projects@gmail.com':alias}, 'Epsilon200')
		contents = ['Results of your Emsi query are attached.', file]
		yag.send(send_to, 'Emsi Data Query', contents)
		
	except:
		pass

		
def parse_custom(custom):
	
	custom_parse = custom.split(',')

	regions = []
	subregions = []
	
	for item in custom_parse:
	
		if (len(item) > 2):
			subregions.append(item)

		else:
			regions.append(item)

	regions = [str(i) for i in regions]
	subregions = [str(i) for i in subregions]	
	
	t.close()
	
	return regions,subregions
		

pid = str(os.getpid())
pidfile = '/var/www/html/emsi/logs/daemon.pid'

if os.path.isfile(pidfile):
	print ('exiting')
	sys.exit(0)

file = open(pidfile,'w')
file.write(pid)
file.close()

file = open('/var/www/html/emsi/logs/log.txt','a')#ensure sys args are happening
file.write(str(datetime.now())+': begin transmission\n')
out_path = '/var/www/html/emsi/output/'

try:

	try:
		country 	= sys.argv[1]
		geography 	= sys.argv[2]
		dataset 	= sys.argv[3]
		metric 		= sys.argv[4]
		start_year	= str(sys.argv[5])
		end_year 	= str(sys.argv[6])
		revision_year = sys.argv[7]
		filename 	= sys.argv[8]
		
		if (((len(sys.argv)) == 10) and (sys.argv[9].find('@')) > 0):
			email = sys.argv[9]
			file.write(str(datetime.now())+': email '+email+'\n')
			
		elif ((len(sys.argv)) == 10):
			custom = sys.argv[9]
			file.write(str(datetime.now())+': regions '+custom+'\n')
			regions,subregions = parse_custom(custom)
			
		else:
			pass
		#so, it's reading custom region into email
		if (len(sys.argv) == 11):
			email = sys.argv[9]
			custom = sys.argv[10]
			
			regions,subregions = parse_custom(custom)

		if (filename[-4:] != '.csv'):
			filename += '.csv'
		
	except Exception as e:
		file.write(str(datetime.now())+': sys.argv not detected +'+e+'\n')
		os.unlink(pidfile)
		sys.exit()

	file.write(str(datetime.now())+country+' '+geography+' '+dataset+' '+metric+'\n')
		
	client_id = 'your_id'
	client_secret = 'your_secret'
	emsi_secret='your_key'
	token_url = 'https://auth.emsicloud.com/connect/token'
	scope = 'emsiauth'
	content_type = 'application/x-www-form-urlencoded'

	'''Authenticate with server'''
	try:
		response = requests.post(token_url,data={
			'grant_type':'client_credentials',
			'client_id':client_id,
			'client_secret':client_secret,
			'scope':scope,
			'Content-Type':content_type
		})

		token = response.json()['access_token']

		expiry = response.json()['expires_in']
		header = {'Authorization': 'bearer '+token,'Content-Type':'application/json'}#'application/json'}
		
		file.write(str(datetime.now())+': response successful \n')
		
	except Exception as e:
		file.write(str(datetime.now())+' response failed \n'+str(e))
		os.unlink(pidfile)

	# meta_url = 'http://agnitio.emsicloud.com/meta/dataset/emsi.{}.{}/{}'.format(country,dataset,revision_year)
	# meta_areas = 'http://agnitio.emsicloud.com/meta/dataset/emsi.{}.{}/{}/area'.format(country,dataset,revision_year)

	# meta_response = requests.get(meta_areas,headers=header).json()
	# mr = pd.DataFrame(meta_response)

	#geog variable will be the standard for the query formula
	#set it depending on what user picked
	'''Get appropriate region list'''
	try:

		print ('reading in data')

		if country == 'ca':
			if geography == 'county':
				geog = pd.read_csv('/var/www/html/emsi/data/ca_csd.csv',encoding='latin1')
				geography = 'csd'
			if geography == 'state':
				geog = pd.read_csv('/var/www/html/emsi/data/ca_prov.csv',encoding='latin1')
				geography = 'province'
			if geography == 'msa':
				geog = pd.read_csv('/var/www/html/emsi/data/ca_cma.csv',encoding='latin1')
				#geog = pd.read_csv('/var/www/html/emsi/data/test_ca_csd.csv',encoding='latin1')
				grouping = pd.read_csv('/var/www/html/emsi/data/ca_cma.csv',encoding='latin1')#temp changed to ab_cma from ca_cma 23.5.18
				geography = 'cma'
				
		if country == 'us':
			if geography == 'county':
				geog = pd.read_csv('/var/www/html/emsi/data/us_counties.csv',encoding='latin1')
			if geography == 'state':
				geog = pd.read_csv('/var/www/html/emsi/data/us_states.csv',encoding='latin1')
			if geography == 'msa':
				geog = pd.read_csv('/var/www/html/emsi/data/us_counties.csv',encoding='latin1')
				grouping = pd.read_csv('/var/www/html/emsi/data/us_msa.csv',encoding='latin1')
		
		aggregate_df = pd.DataFrame()
		
		#test script function
		file.write(str(datetime.now())+': file read successfully -'+geography+' -'+str(len(geog))+'\n')
		
	except Exception as e:
		file.write(str(datetime.now())+': file read failed: '+str(e)+'\n')
		os.unlink(pidfile)

	
	'''Filter based on custom data, if applicable'''
	'''
	#Regions, no subregions
	if ((len(regions)>0) and (len(subregions) == 0)):
		file.write(str(datetime.now())+': only regions found: '+regions+'\n')
		geog['temp'] = geog['id'].apply(lambda x: str(x)[:2])
		geog = geog[geog['temp'].isin(regions)]
		

	elif (len(subregions) > 0):
		file.write(str(datetime.now())+': subregions detected: '+subregions+'\n')
		geog = geog[geog['temp'].isin(subregions)]
		
		
	else:
		file.write(str(datetime.now())+': regional catasrophe: '+regions+subregions+'\n')
		
	'''	
	'''
	LQ query

	{'metrics':[
		{'name':metric+'.'+str(year)
			'operation':{
				'name':LocationQuotient'
				'geoparent':0' #0->national LQ #state_id->state LQ
				'along':dataset
				}
			}
		],
	{'constraints':...kept the same
	}

	Additional processing for custom regions:
	CMA/MSA -> list of cma/msa and consituent CSDs/counties -> groupby()
	Custom List -> user selected fine geography/naics/nocs
				->individual province/county/cma/LUF/economic region
	'''	

	final_df = pd.DataFrame()
	#geoparent:0 ->national LQ #state_id->state LQ
	lq_type = 0


	if dataset == 'occupation':
		valName = 'NOC/SOC'
	else:
		valName = 'NAICS'


	try:
		for year in range(int(start_year),int(end_year)+1):
			
			aggregate_df = pd.DataFrame()
			
			for region in range(0,len(geog)):
				#file.write(str(datetime.now())+': trying -'+str(year)+'.'+str(geog.iloc[region,0])+'.'+metric+'\n')
				query = {'metrics':[
							{'name':metric+'.'+str(year)}
							],
						'constraints':[
							{'dimensionName':'Area','map':{str(geog.iloc[region,1].encode('utf8')):[str(geog.iloc[region,0])]}},
							{'dimensionName':'ClassOfWorker','map':{'Employees':['1']}},
							{'dimensionName':dataset.capitalize(),'asIdentity':True}
							],
						'zeroFill':False
					}

				response = requests.post('http://agnitio.emsicloud.com/emsi.{}.{}/{}'.format(country,dataset,revision_year),
										 headers=header,
										 data=json.dumps(query))
				
				data = response.content.decode('utf-8')
				data = pd.read_json(data,orient='index',typ='series')
				data_dict = {}

				for i in range(len(data[0])):
					data_dict[data[0][i]['name']] = data[0][i]['rows']

				df = pd.DataFrame.from_dict(data_dict,orient='columns')
				#df['Year'] = year
				#metric.year -> Value
				
				df.columns = ['Area','ClassOfWorker',valName,'value']
				aggregate_df = aggregate_df.append(df,ignore_index=True)
				aggregate_df['year'] = year
				aggregate_df['indicator'] = metric
				
				
			if len(final_df) > 0:
				#final_df = pd.merge(left=final_df,right=aggregate_df,on=['Area','ClassOfWorker'])
				final_df = final_df.append(aggregate_df,ignore_index=True)
			else:
				final_df = final_df.append(aggregate_df,ignore_index=True)
				file.write(str(datetime.now())+': appending data \n')
		
		
		final_df = pd.merge(final_df,geog[['name','id']],left_on='Area',right_on='name',how='left')
		final_df = final_df[['name','id','year',valName,'indicator','value']]
		
		if (geography == 'msa') or (geography == 'cma'):
			'''
			1.Join CMA/MSA names to CSD/County
			2.Groupby CMA/MSA name.
			'''
			file.write(str(datetime.now())+': creating CMA/MSA data \n')
			as_cma = pd.merge(final_df,grouping,left_on='id',right_on='id',how='left')
			final_df = as_cma.groupby(['msa','msa_id','year',valName,'indicator'],as_index=False)['value'].sum()
			
		final_df.to_csv(out_path+filename,index=False)
		file.write(str(datetime.now())+': data saved as: '+filename+' \n')
		

		try:
			z = zipfile.ZipFile(out_path+filename[:-4]+'.zip',mode='w')
			z.write(out_path+filename, basename(out_path+filename),compress_type=compression)
			z.close()
			os.unlink(out_path+filename)
			
			if (len(email)>0):
				file.write(str(datetime.now())+': sending email to '+email+'\n')
				send_mail(email,out_path+filename[:-4]+'.zip')
			
		except:
			file.write(str(datetime.now())+': zipping failed\n')
			
		file.close()
		os.unlink(pidfile)
		
		
		
	except Exception as e:
		
		exc_type, exc_obj, exc_tb = sys.exc_info()
		fname = os.path.split(exc_tb.tb_frame.f_code.co_filename)[1]
		print (e,exc_tb.tb_lineno)
		file.write(str(datetime.now())+': data query error - line '+str(exc_tb.tb_lineno)+' '+str(e)+'\n')
		file.close()
		os.unlink(pidfile)

except Exception as e:
	file.write(str(datetime.now())+': error - line '+str(exc_tb.tb_lineno)+' '+str(e)+'\n')
	file.close()
	os.unlink(pidfile)
