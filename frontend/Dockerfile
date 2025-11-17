FROM node:20-alpine

# Set working directory
WORKDIR /app

# Copy package files
COPY package.json package-lock.json ./

# Install dependencies
RUN npm ci || npm install

# Copy application files
COPY . .

# Build the application
RUN npm run build

# Install serve globally for production
RUN npm install -g serve

# Expose port
EXPOSE $PORT

# Start server
CMD serve -s dist -l $PORT

