# Mariem_Final_Individual_Project_Webtech

## Project Overview
IDTrack is a web-based national ID application system for Mauritania, allowing residents to apply for national ID cards and residence permits online.

## Database Setup

### 1. Create and Import Database
```bash
# Create the database
mysql -u root -p -e "CREATE DATABASE idtrack;"

# Import the schema
mysql -u root -p idtrack < database/schema.sql

# Verify tables were created
mysql -u root -p idtrack -e "SHOW TABLES;"
