#!/bin/bash
# Generate database diagram using SchemaSpy

# Install SchemaSpy and graphviz if needed
# brew install graphviz
# Download SchemaSpy from https://github.com/schemaspy/schemaspy/releases

# Configuration
DB_NAME="ABEDEPORT"
DB_HOST="localhost"
DB_PORT="3306"
DB_USER="root"
DB_PASS="your_password"
OUTPUT_DIR="./database/diagrams"

# Create output directory
mkdir -p "$OUTPUT_DIR"

# Run SchemaSpy
java -jar schemaspy.jar \
  -t mysql \
  -host "$DB_HOST:$DB_PORT" \
  -db "$DB_NAME" \
  -u "$DB_USER" \
  -p "$DB_PASS" \
  -o "$OUTPUT_DIR" \
  -s "$DB_NAME"

echo "Diagram generated in $OUTPUT_DIR/diagrams/summary/relationships.real.large.png"
