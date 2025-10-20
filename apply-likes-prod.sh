#!/bin/bash

# Simple script to apply likes_comments.sql to production
# No backup, no verification, just apply the SQL

docker exec -i abedeport-db mysql -u root -pXYGVoqJUduGKWZyUCLvF ABEDEPORT < database/likes_comments_no_samples.sql

echo "Done."
