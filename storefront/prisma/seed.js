const { PrismaClient } = require("@prisma/client");
const fs = require("fs");
const path = require("path");

const prisma = new PrismaClient();

const run = async () => {
  const dataPath = path.join(process.cwd(), "data", "products.json");
  const products = JSON.parse(fs.readFileSync(dataPath, "utf-8"));

  for (const product of products) {
    await prisma.product.upsert({
      where: { id: product.id },
      update: {
        name: product.name,
        description: product.description,
        price: product.price,
        badge: product.badge ?? null,
        category: product.category,
        image: product.image,
        isVisible: true,
      },
      create: {
        id: product.id,
        name: product.name,
        description: product.description,
        price: product.price,
        badge: product.badge ?? null,
        category: product.category,
        image: product.image,
        isVisible: true,
      },
    });
  }
};

run()
  .catch((error) => {
    console.error(error);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
