import fs from "fs";
import path from "path";
import mime from "mime-types";
import { S3Client, PutObjectCommand } from "@aws-sdk/client-s3";
import dotenv from "dotenv";

dotenv.config({path: '.env'});

// Read credentials and region from environment variables.
const bucketAccessKeyId = process.env.AWS_ACCESS_KEY_ID;
const bucketSecretAccessKey = process.env.AWS_SECRET_ACCESS_KEY;
const bucketEndpoint = process.env.AWS_ENDPOINT;
const bucketName = process.env.AWS_BUCKET;

// Configure S3 client with your access and secret keys.
const s3Client = new S3Client({
    region: 'us-east-1', // Change to your bucket's region.
    endpoint: bucketEndpoint, // you can skip this for S3.
    credentials: {
        accessKeyId: bucketAccessKeyId,
        secretAccessKey: bucketSecretAccessKey,
    },
});

// Upload files to CDN bucket.
const directoryPath = "./public/build";
const assetsPaths = fs.readdirSync(directoryPath, { recursive: true });

assetsPaths.forEach((assetPath) => {
    const filePath = path.join(directoryPath, assetPath);

    if (fs.lstatSync(filePath).isFile()) {
        console.log(`Uploading file: ${assetPath}`);

        // Determine the MIME type based on the file extension
        const contentType = mime.lookup(filePath) || 'application/octet-stream';

        s3Client.send(new PutObjectCommand({
            Bucket: bucketName,
            Key: `build/${assetPath}`,
            Body: fs.readFileSync(filePath),
            ContentType: contentType,
            ACL: 'public-read', // Make the file publicly readable.
        })).then((data) => {
            console.log(`File "${assetPath}" uploaded successfully.`);
        }).catch((error) => {
            console.error(`Error uploading file "${assetPath}":`, error);
        });
    }
});
