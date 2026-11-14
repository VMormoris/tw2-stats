FROM python:3.13-slim

ENV TZ=Europe/London \
    PYTHONUNBUFFERED=1

WORKDIR /dumper
COPY dumper-app/requirements.txt .
RUN pip install --no-cache-dir --root-user-action=ignore -r requirements.txt

# The emit tokens are cached in .cache, which is a volume
RUN useradd --system dumper && mkdir .cache && chown dumper .cache
COPY dumper-app/*.py .
USER dumper

CMD ["python", "dumper-app.py"]
