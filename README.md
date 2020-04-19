### Save Companies Information
> Store companies and industries using external Website.

### Installation
```bash
# Create company database
# Import the company.sql file
```
### API list

i) Store Industries in database <br />
http://localhost/company/api/save_industries <br />
 <br />
ii) Store Companies in database by section and page <br />
http://localhost/company/api/save_companies_by_section?section=F&page=3 <br />
 <br />
iii) Store Company using uri segment <br />
http://localhost/company/api/save_company?company_uri=prozone-intu-properties-limited-1 <br />
 <br />

### Error Codes
status:1 -> Success (status:200) <br />
status:2 -> Company or industry details already exists in database (status:402) <br />
status:3 -> Can't get the data from website (status:403) <br />
status:4 -> Parameter missing (status:405) <br />
status:5 -> Server error (status:500) <br />