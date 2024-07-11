const puppeteer = require('puppeteer');
const fs = require('fs-extra');
const path = require('path');
const baseUrl =
 'https://www.makov.sk/obec-2/centrum-socialnych-sluzieb/zivot-v-zps/';
const maxPages = 2;

(async () => {
 const browser = await puppeteer.launch({ headless: true });
 const page = await browser.newPage();

 const outputDir = './scraped_data';
 await fs.ensureDir(outputDir);
 const imagesDir = path.join(outputDir, 'images');
 await fs.ensureDir(imagesDir);

 const data = [];

 for (let pageNum = 1; pageNum <= maxPages; pageNum++) {
  const url = `${baseUrl}?page=${pageNum}`;
  await page.goto(url, { waitUntil: 'networkidle2' });

  const posts = await page.$$eval('.event-link', (links) =>
   links.map((link) => ({
    title: link.textContent.trim(),
    url: link.href,
   }))
  );

  for (const post of posts) {
   try {
    await page.goto(post.url, { waitUntil: 'networkidle2' });

    const postData = await page.evaluate(() => {
     const titleElement = document.querySelector('.gcm-main h1');
     const dateUploadElement = document.querySelector('.date-insert-value');
     const dateUpdateElement = document.querySelector('.date-update-value');
     const authorElement = document.querySelector(
      '.event-info-value.event-author'
     );
     const textElements = document.querySelectorAll('.event-text.editor p');
     const imageElements = document.querySelectorAll('.card-img-top');

     const title = titleElement
      ? titleElement.textContent.trim()
      : 'No title found';
     const dateUploaded = dateUploadElement
      ? dateUploadElement.textContent.trim()
      : 'No date found';
     const dateUpdated = dateUpdateElement
      ? dateUpdateElement.textContent.trim()
      : '';
     const author = authorElement
      ? authorElement.textContent.trim()
      : 'No author found';
     const text = Array.from(textElements)
      .map((p) => p.textContent.trim().replace(/\s+/g, ' '))
      .join('\n');
     const images = Array.from(imageElements).map((img) => img.src);

     return {
      title,
      date: { uploaded: dateUploaded, updated: dateUpdated },
      author,
      text,
      images,
     };
    });

    console.log(`Scraped: ${postData.title}`);

    const sanitizedTitle = postData.title.replace(/\s+/g, '_');

    for (let i = 0; i < postData.images.length; i++) {
     const imageUrl = postData.images[i];
     const imageFileName = `${sanitizedTitle}_${i + 1}.jpg`;
     const imageFilePath = path.join(imagesDir, imageFileName);

     const viewSource = await page.goto(imageUrl);
     await fs.writeFile(imageFilePath, await viewSource.buffer());
    }

    data.push({
     title: postData.title,
     date: postData.date,
     author: postData.author,
     text: postData.text,
     images: postData.images.map((imageUrl, index) =>
      path.join('images', `${sanitizedTitle}_${index + 1}.jpg`)
     ),
    });
   } catch (error) {
    console.error(`Error scraping post: ${post.url}`, error);
   }
  }
 }

 await fs.writeJson(path.join(outputDir, 'data.json'), data, { spaces: 2 });

 await browser.close();
 console.log('Scraping completed successfully.');
})();
