## Natural Language to Sql
Purpose of this tool to make an **easy access to database** for the users **who are not qualified** in IT.
An SQL - might seem a self-explanatory for an IT engineer. But still too strict for an ordinary office worker.

Advantages
- **no need to develop** interfaces to work with database
- customizable for every person
- no strict rules, actions have a lot of **aliases**
- speed due to minimalistic concept
- **any language** can be implemented
- easy connection with voice **assistants** (Google assistant, Amazon Alexa)

## Some queries
```
get users
find all orders
order with id=3
find users who have type=2
how many users who have type=1
first user
last order
last 3 orders
```
##### SELECT
```
search
show  
find      => SELECT
get  
select 
// or noting at all, it will be assumed as select

count 
how many    =>   COUNT
how much


where
who have
which have      => WHERE,
who has
which have
with
	
```

## TODO
- Resolving foreign keys
- Single record view
- Saving favorite queries
- Dashboard
- add other language
- Move to VueJS
- More intuitive and short notation like:
  ```
  "user 13" => SELECT * FROM users WHERE id=13 
  "user email john@gmail.com " => SELECT * FROM users WHERE email=john@gmail.com
  ```
- Add more aggregate functions
- Relation tables for a single record view
- Installation interface with setting accessible tables, defining relations and language translations for db columns
- Working with date
- Security checks for preventing mysql injections 


